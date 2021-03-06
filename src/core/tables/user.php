<?php

return [
  'name'=> 'user',
  'title'=> 'Users',
  'pagination'=> 15,
  'tools'=>['add_popup','csv'],
  'commands'=>['edit_popup','delete'],
  'id'=>'id',
  'lang'=>'core/lang/admin/',
  'meta_table'=>['usermeta', 'user_id', 'vartype', 'value'],
  'js'=>['src/core/tables/user.js'],
  'permissions'=>[
    'read'=>['admin','admin_user'],
    'create'=>['admin','admin_user'],
    'update'=>['admin','admin_user'],
    'delete'=>['admin']
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
      'meta_key'=>'photo'
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
      'input_type'=>'role',
      'edit'=>true,
      'meta_key'=>'role',
      'options'=>[],
      'qoptions'=>"SELECT `id`,`userrole` FROM userrole"
    ],
    'active'=> [
      'type'=>'checkbox',
      'title'=>'Active',
      'qtype'=>'INT(1) DEFAULT 1'
    ],
    'reset_code'=> [
      'list'=>false,
      'edit'=>false,
      'create'=>false,
      'qtype'=>'varchar(60)'
    ],
    'created'=> [
      'title'=>'Created',
      'type'=>'date',
      'list'=>false,
      'edit'=>false,
      'create'=>false,
      'qtype'=>'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ],
    'updated'=> [
      'title'=>'Updated',
      'type'=>'date',
      'list'=>false,
      'edit'=>false,
      'create'=>false,
      'qtype'=>'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'manager'=> [
      'type'=>'meta',
      'title'=>'Manager',
      'list'=>false,
      'meta_key'=>'manager_id',
      'options'=>[''=>'-'],
      'qoptions'=>'SELECT `id`,`username` FROM user;'
    ]
  ],
  'events'=>[
    ['change',function (&$row) {
      if (isset($row['userrole'])) {
        $roles = is_array($row['userrole'])? $row['userrole']: explode(',', $row['userrole']);
        $level = Gila\User::level(Gila\Session::userId());
        foreach ($roles as $roleId) {
          if ($level<Gila\User::roleLevel($roleId)) {
            http_response_code(500);
            exit;
          }
        }
      }
      if (isset($row['pass'])) {
        if (substr($row['pass'], 0, 7) != "$2y$10$") {
          $row['pass'] = Config::hash($row['pass']);
        }
      }
    }]
  ]
];
