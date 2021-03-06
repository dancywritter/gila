<?php

View::stylesheet('src/featured_grid/assets/style.css');


Event::listen('slide', function () {
  if (Router::controller()=='blog') {
    $align = Config::option('featured_grid.align', 'center');
    echo '<style>.featured-posts .img { background-position: center ';
    echo $align.';}</style>';
    echo '<div class="featured-posts row">';
    $params=['posts'=>4];
    if (Config::option('featured_grid.category')!='') {
      $params['category']=Config::option('featured_grid.category');
    }
    foreach (blog::posts($params) as $p) {
      $srcset = View::thumbSrcset($p['img'], [800,300]);
      echo "<div>";
      echo "<a href=\"".Config::url('blog/'.$p['id'].'/'.$p['slug'])."\">";
      echo "<div class=\"img\" style=\"background-image: url('{$srcset[0]}');";
      echo "background-image: -webkit-image-set(url({$srcset[0]}) 1x,";
      echo " url({$srcset[1]}) 2x);\"></div>";
      echo "<div class=\"featured-title\">{$p['title']}</div>";
      echo "</a></div>";
    }
    echo '</div>';
  }
});
