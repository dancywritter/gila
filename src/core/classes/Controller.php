<?php

class Controller
{
  static function admin()
  {
    if(Session::key('user_id')==0) {
      Gila::addLang('core/lang/login/');
      if(Session::waitForLogin()>0) {
        View::alert('error', __('login_error_msg2'));
      } else if (isset($_POST['username']) && isset($_POST['password'])) {
        View::alert('error', __('login_error_msg'));
      }
      View::renderFile('login.php');
      exit;
    }
  }

  static function access($pri)
  {
    if(Gila::hasPrivilege($pri)===false) {
      http_response_code(403);
      exit;
    }
  }

  public function __call($method, $args)
  {
    if (isset($this->$method)) {
      $func = $this->$method;
      return call_user_func_array($func, $args);
    }
  }
}