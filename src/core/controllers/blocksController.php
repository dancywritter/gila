<?php
use Gila\Page;
use Gila\Post;
use Gila\Config;
use Gila\View;
use Gila\Event;
use Gila\Router;

class blocks extends Gila\Controller
{
  private static $draft = false;

  public function __construct()
  {
    self::admin();
    Config::addLang('core/lang/admin/');
  }

  public function indexAction()
  {
    global $db;
    $table = Router::request('t');
    $id = Router::get('id', 2);
    $widgets = self::readBlocks($table, $id);
    $title = $db->value("SELECT title FROM $table WHERE id=?;", [$id]);
    View::set('contentType', $table);
    View::set('id', $id);
    View::set('isDraft', self::$draft);
    View::set('widgets', $widgets);
    View::set('title', $title);
    View::renderAdmin("content-block.php", "blocks");
  }

  public function popupAction()
  {
    $table = Router::request('t');
    $id = Router::get('id', 2);
    $widgets = self::readBlocks($table, $id);
    View::set('contentType', $table);
    View::set('id', $id);
    View::set('isDraft', self::$draft);
    View::set('widgets', $widgets);
    echo '<!DOCTYPE html><html>';
    View::head();
    echo '<body>';
    View::renderFile("content-block.php", "blocks");
    echo '</body></html>';
  }

  public function editAction()
  {
    global $db;
    if ($id = Router::get('id', 2)) {
      $idArray = explode('_', $id);
      View::set('widget_id', $id);
      View::set('contentType', $idArray[0]);
      View::set('id', $idArray[1]);
      View::set('type', $_GET['type']);
      View::set('pos', $idArray[2]);
      View::set('widgets', self::readBlocks($idArray[0], $idArray[1]));
      View::renderFile('edit_block.php', 'blocks');
    }
  }

  public function updateAction()
  {
    global $db;
    $id = $_POST['widget_id'];
    $idArray = explode('_', $id);
    $content = $idArray[0];
    $id = (int)$idArray[1];
    $pos = (int)$idArray[2];
    $widgets = self::readBlocks($content, $id);
    if ($type = $widgets[$pos]['_type']) {
      $fields = Gila\Widget::getFields($type);
      $widget_data = $_POST['option'] ?? [];

      foreach ($widget_data as $key=>$value) {
        $allowed = $fields[$key]['allow_tags'] ?? false;
        $widget_data[$key] = Gila\HtmlInput::purify($widget_data[$key], $allowed);
      }
      $widget_data['_type'] = $type;
      $widgets[$pos] = $widget_data;
      self::updateBlocks($content, $id, $widgets);
      echo json_encode($widgets);
    }
  }

  public function posAction()
  {
    global $db;
    $rid = $_POST['id'];
    $idArray = explode('_', $rid);
    $content = $idArray[0];
    $id = (int)$idArray[1];
    $pos = (int)$idArray[2];
    $newpos = (int)$_POST['pos'];
    $widgets = self::readBlocks($content, $id);

    if ($newpos<0 || $newpos>count($widgets)-1) {
      echo json_encode($widgets);
      return;
    }

    for ($i=$pos; $i!=$newpos; $i+=$newpos<=>$pos) {
      // swap blocks
      $nexti = $i+($newpos<=>$pos);
      $tmp = $widgets[$i];
      $widgets[$i] = $widgets[$nexti];
      $widgets[$nexti] = $tmp;
      $nextrid = $content.'_'.$id.'_'.$nexti;
      $wfile = SITE_PATH.'tmp/stacked-wdgt'.$rid.'.jpg';
      $nextwfile = SITE_PATH.'tmp/stacked-wdgt'.$nextrid.'.jpg';
      if (file_exists($wfile)) {
        rename($wfile.'.json', SITE_PATH.'tmp/tmp_wgtjpgjson');
        rename($wfile, SITE_PATH.'tmp/tmp_wgtjpg');
      }
      if (file_exists($nextwfile)) {
        rename($nextwfile.'.json', $wfile.'.json');
        rename($nextwfile, $wfile);
      }
      if (file_exists(SITE_PATH.'tmp/tmp_wgtjpg')) {
        rename(SITE_PATH.'tmp/tmp_wgtjpgjson', $nextwfile.'.json');
        rename(SITE_PATH.'tmp/tmp_wgtjpg', $nextwfile);
      }
    }

    self::updateBlocks($content, $id, $widgets);
    echo json_encode($widgets);
  }


  public function createAction()
  {
    global $db;
    $rid = $_POST['id'];
    $idArray = explode('_', $rid);
    $content = $idArray[0];
    $id = $idArray[1];
    $pos = (int)$idArray[2];
    $widgets = self::readBlocks($content, $id)??[];
    $new = ['_type'=>$_POST['type']];
    $type = strtr($_POST['type'], ['/'=>'','\\'=>'','.'=>'']);
    $fields = Gila\Widget::getFields($type);
    foreach ($fields as $key=>$field) {
      if (isset($field['default'])) {
        $new[$key] = $field['default'];
      }
    }
    array_splice($widgets, $pos, 0, [$new]);
    self::updateBlocks($content, $id, $widgets);
    echo json_encode($widgets);
  }

  public function deleteAction()
  {
    global $db;
    $rid = $_POST['id'];
    $idArray = explode('_', $rid);
    $content = $idArray[0];
    $id = $idArray[1];
    $pos = (int)$idArray[2];
    $widgets = self::readBlocks($content, $id);
    array_splice($widgets, $pos, 1);
    self::updateBlocks($content, $id, $widgets);
    echo json_encode($widgets);
  }

  public function displayAction()
  {
    $content = Router::get('t', 1);
    $id = Router::get('id', 2);
    $blocks = self::readBlocks($content, $id);
    if ($content=="page" && $r = Page::getByIdSlug($id, false)) {
      View::set('title', $r['title']);
      View::set('text', View::blocks($blocks, 'page'.$r['id'], true));
      if ($r['template']===''||$r['template']===null) {
        View::render('page.php');
      } else {
        View::renderFile('page--'.$r['template'].'.php');
      }
      echo '<style>html{scroll-behavior: smooth;}</style>';
      $isDraft = self::$draft;
      include __DIR__.'/../views/content-block-edit.php';
    } elseif ($content=="post" && $r = Post::getByIdSlug($id)) {
      View::set('title', $r['title']);
      View::set('text', $r['post'].View::blocks($blocks, 'post'.$r['id'], true));
      View::render('single-post.php');
      echo '<style>html{scroll-behavior: smooth;}</style>';
    } else {
      View::renderFile('blocks-display-head.php', 'blocks');
      Event::fire('body');
      echo View::blocks($blocks, 'page'.$id, true);
      echo '</article></body>';
      Event::fire('footer');
    }
  }

  public function saveAction()
  {
    $rid = $_POST['id'];
    $idArray = explode('_', $rid);
    $content = $idArray[0];
    $id = $idArray[1];
    $widgets = self::readBlocks($content, $id);
    self::saveBlocks($content, $id, $widgets);
  }

  public function discardAction()
  {
    $rid = $_POST['id'];
    $idArray = explode('_', $rid);
    $content = $idArray[0];
    $id = $idArray[1];
    $draftFile = LOG_PATH.'/blocks/'.$content.$id.'.json';
    unlink($draftFile);
    $widgets = self::readBlocks($content, $id);
    echo json_encode($widgets);
  }

  public static function readBlocks($content, $id)
  {
    global $db;
    $draftFile = LOG_PATH.'/blocks/'.$content.$id.'.json';
    if (file_exists($draftFile)) {
      $json = file_get_contents($draftFile);
      self::$draft = true;
    } else {
      $content = $db->res($content);
      $json = $db->value("SELECT blocks FROM `$content` WHERE id=?;", [$id]);
      self::$draft = false;
    }
    return json_decode($json, true)??[];
  }

  public static function updateBlocks($content, $id, $blocks)
  {
    $draftFile = LOG_PATH.'/blocks/'.$content.$id.'.json';
    $json = json_encode($blocks);
    Config::dir(LOG_PATH.'/blocks/');
    file_put_contents($draftFile, $json);
  }

  public static function saveBlocks($content, $id, $blocks)
  {
    global $db;
    $return = [];
    foreach ($blocks as $w) {
      if ($w!==null) {
        $return[] = $w;
      }
    }
    $db->query("UPDATE $content SET `blocks`=? WHERE id=?;", [json_encode($return), $id]);
    $draftFile = LOG_PATH.'/blocks/'.$content.$id.'.json';
    unlink($draftFile);
  }
}
