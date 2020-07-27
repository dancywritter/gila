<?php

$widget_areas = ['x'=>'(None)'];
foreach (Config::$widget_area as $value) {
  $widget_areas[$value] = $value;
}
$widgets = [];
foreach (Config::$widget as $k=>$value) {
  $widgets[$k] = $k;
}

return [
    'name'=> 'widget',
    'title'=> 'Widgets',
    'id'=>'id',
    'tools'=>['add_popup'],
    'commands'=>['edit_widget','delete'],
    'list'=> ['id','title','widget','area','pos','active'],
    'csv'=> ['id','title','widget','area','pos','active'],
    'lang'=>'core/lang/admin/',
    'js'=>['src/core/tables/widget.js'],
    'permissions'=>[
        'create'=>['admin'],
        'update'=>['admin'],
        'delete'=>['admin']
    ],
    'search_box'=> true,
    'search_boxes'=> ['area','widget'],
    'fields'=> [
        'id'=> ['title'=>'ID', 'edit'=>false],
        'widget'=> ['title'=>'Type', 'type'=>'select', 'options'=>$widgets, 'create'=>true],
        'title'=> ['title'=>'Title'],
        'area'=> ['title'=>'Widget Area', 'type'=>'select', 'options'=>$widget_areas],
        'pos'=> ['title'=>'Position','default'=>1],
        'active'=> [
          'title'=>'Active',
          'type'=>'checkbox','edit'=>true,'create'=>false
        ],
        'data'=> [
          'title'=>'Data', 'list'=>false, 'edit'=>false,
          'type'=>'text','allow_tags'=>true
        ],
    ],
    'events'=>[
        ['change',
        function (&$row) {
          if (!isset($row['data']) || $row['data']!==null) {
            return;
          }
          $wdgt_options = include 'src/'.Config::$widget[$row['widget']].'/widget.php';
          if (isset($options)) {
            $wdgt_options = $options;
          }
          $default_data=[];
          foreach ($wdgt_options as $key=>$op) {
            if (isset($op['default'])) {
              $def=$op['default'];
            } else {
              $def='';
            }
            $default_data[$key]=$def;
          }

          $row['data']=json_encode($default_data);
        }]
    ]
];
