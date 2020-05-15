<?php
namespace core\models;
use View;
use Router;

class Profile
{

  static function postUpdate($user_id)
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    if (Router::post('submit-btn')==='submited'){
      User::updateName($user_id, strip_tags($_POST['gila_username']));
      User::meta($user_id, 'twitter_account', strip_tags($_POST['twitter_account']));
      View::alert('success', __('_changes_updated'));
    }

    if (Router::post('submit-btn')=='password'){
      $usr = User::getById($user_id);
      $pass = $_POST['new_pass'];
      if(password_verify($_POST['old_pass'], $usr['pass'])) {
        if(strlen($pass) > 4 ) {
          if($pass===$_POST['new_pass2']) {
            if(User::updatePassword($user_id, $pass)) {
              View::alert('success', __('_changes_updated'));
            }
          } else {
            View::alert('alert', __('New passwords do not match'));
          }
        } else {
          View::alert('alert', __('New password too small'));
        }
      } else {
        View::alert('alert', __('Password incorrect'));
      }
    }

    if (Router::post('token')==='generate') {
      $token = self::generateToken();
      while(count(User::getIdsByMeta('token', $token)) > 0) {
        $token = self::generateToken();
      }
      User::meta($user_id, 'token', $token);
      View::alert('success', __('_changes_updated'));
    }

    if (Router::post('token')==='delete') {
      User::meta($user_id, 'token', '');
      View::alert('success', __('_changes_updated'));
    }
  }

  static function generateToken() {
    $token = '';
    while(strlen($token) < 160) {
      $token .= hash('sha512', uniqid(true));
    }
    return substr($token, 0, 160);
  }

}
