<?php

namespace Gila;

class Menu
{
  private static $active = false;

  public static function defaultData()
  {
    global $db;
    $widget_data = (object) array('type'=>'menu','children' => []);
    $widget_data->children[] = ['type'=>'link','url'=>'','name'=>__('Home')];
    $ql = "SELECT id,title FROM postcategory;";
    $pages = $db->get($ql);
    foreach ($pages as $p) {
      $widget_data->children[] = ['type'=>"postcategory",'id'=>$p[0]];
    }
    foreach (Page::genPublished() as $p) {
      $widget_data->children[] = ['type'=>'page','id'=>$p[0]];
    }
    return (array) $widget_data;
  }

  public static function convert($data)
  {
    $items = [];
    if ($type = $data['type']) {
      if ($data['children']) {
        $children = [];
        foreach ($data['children'] as $mi) {
          $children[] = self::convert($mi);
        }
      }
      if ($type=='menu') {
        return $children;
      }
      if ($type=='dir') {
        return ['name'=>$data['name']??'', 'url'=>$data['url']??'#',
        'children'=>$children];
      }
      if ($type=='page') {
        if ($r=Page::getById(@$data['id'])) {
          $url = $r['slug'];
          $name = $r['title'];
          return ['name'=>$name, 'url'=>$url];
        }
      }
      if ($type=='postcategory') {
        global $db;
        $ql = "SELECT id,title,slug FROM postcategory WHERE id=?;";
        $res = $db->query($ql, @$data['id']);
        while ($r=mysqli_fetch_array($res)) {
          $url = "category/".$r[0].'/'.$r[2];
          $name = $r[1];
        }
        return ['name'=>$name, 'url'=>$url];
      }
      if ($res = MenuItemTypes::get($data)) {
        list($url, $name) = $res;
        return ['name'=>$name, 'url'=>$url];
      }
    }
    return ['name'=>$data['name'], 'url'=>$data['url']];
  }

  public static function getHtml($items, $base="")
  {
    $html = "";
    self::$active = false;
    foreach ($items as $key => $item) {
      $url = $item['url'] ?? $item[1];
      $label = $item['name'] ?? $item[0];
      if (isset($item['access'])) {
        if (!Session::hasPrivilege($item['access'])) {
          continue;
        }
      }
      if (isset($item['icon'])) {
        $icon = 'fa-'.$item['icon'];
      } else {
        $icon='';
      }
      $url = ($url=='#')? 'javascript:void(0)': htmlentities($url);
      $badge = "";
      if (isset($item['counter'])) {
        $c = is_callable($item['counter'])? $item['counter'](): $item['counter'];
        if ($c>0) {
          $badge = " <span class=\"g-badge\">$c</span>";
        }
      }
      $liClass = '';
      if (isset($item['children'])) {
        $liClass .= 'dropdown';
        $childrenHtml = "<ul class=\"dropdown-menu\">";
        $childrenHtml .= Menu::getHtml($item['children'], $base);
        $childrenHtml .= "</ul>";
        //$childrenHtml .= "</ul>";
        if (self::$active===true) {
          self::$active = false;
          $liClass .= ' active';
        }
      } else {
        $childrenHtml = '';
      }
      if ($url==$base) {
        self::$active = true;
        $liClass .= ' active';
      }
      $liClass = $liClass!==''? ' class="'.$liClass.'"': '';
      $html .= "<li$liClass><a href='".$url."'><i class='fa {$icon}'></i>";
      $html .= " <span>".Config::tr("$label")."</span>$badge</a>";
      $html .= $childrenHtml."</li>";
    }
    return $html;
  }
}
