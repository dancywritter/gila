<?php

return [
  'name'=> 'user',
  'title'=> 'Users',
  'pagination'=> 15,
  'tools'=>['add','csv'],
  'commands'=>['edit_popup'],
  'id'=>'id',
  'lang'=>'core/lang/admin/',
  'permissions'=>[
    'create'=>['admin','admin_user'],
    'read'=>['admin','admin_user'],
    'update'=>['admin','admin_user'],
    'delete'=>false
  ],
  'csv'=> ['id','username','email'],
  'fields'=> [
    'id'=> [
      'title'=>'ID',
      'edit'=>false
    ],
    'photo'=> [
      'type'=>'meta',
      'input-type'=>'media',
      'title'=>'Photo',
      'mt'=>['usermeta', 'user_id', 'value'],
      'metatype'=>['vartype', 'photo']
    ],
    'username'=> [
      'title'=>'Name',
      'qtype'=>'varchar(80)'
    ],
    'email'=> [
      'title'=>'Email',
      'qtype'=>'varchar(80) UNIQUE'
    ],
    'pass'=> [
      'list'=>false,
      'type'=>'password',
      'title'=>'Password',
      'qtype'=>'varchar(120)'
    ],
    'userrole'=> [
      'title'=>'Roles',
      'type'=>'meta',
      'input_type'=>'select2',
      'edit'=>true,
      'mt'=>['usermeta', 'user_id', 'value'],
      'metatype'=>['vartype', 'role'],
      'options'=>[],
      'qoptions'=>'SELECT `id`,`userrole` FROM userrole;'
    ],
    'active'=> [
      'type'=>'checkbox',
      'title'=>'Active',
      'qtype'=>'INT(1) DEFAULT 1'
    ],
    'manager'=> [
      'type'=>'meta',
      'title'=>'Manager',
      'list'=>false,
      'mt'=>['usermeta', 'user_id', 'value'],
      'metatype'=>['vartype', 'manager_id'],
      'options'=>[''=>'-'],
      'qoptions'=>'SELECT `id`,`username` FROM user;'
    ]
  ],
  'events'=>[
    ['change',function(&$row){
      if(isset($row['pass'])) if( substr( $row['pass'], 0, 7 ) != "$2y$10$" )
        $row['pass'] = Gila::hash($row['pass']);
    }]
  ]
];
