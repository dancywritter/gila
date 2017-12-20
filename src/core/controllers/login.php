<?php


use core\models\user as user;

class login extends controller
{

    function __construct ()
    {

    }

	function indexAction()
    {
        if(session::key('user_id')>0) {
           echo "<meta http-equiv='refresh' content='0;url=".gila::config('base')."' />";
           exit;
        }
		include 'src/core/views/login.php';
    }

    function callbackAction()
    {
        event::fire('login.callback');
    }

	function registerAction()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && event::get('recaptcha',true)) {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $name = $_POST['name'];
            if(user::getByEmail($email)) {
                // throw error
            }
            else {
                // register the user
                user::create($email,$password,$name);
            }
        }
		include 'src/core/views/register.php';
    }

	function password_resetAction()
	{
		if(isset($_GET['rp'])) {
            $r = user::getByResetCode($_GET['rp']);
			if (!$r) {
            echo 'This reset password code has been expired or used.';
  				  exit;
			}
			else if(isset($_POST['pass'])) {
				$idUser=$r[0];
                user::updatePassword($idUser,$_POST['pass']);
				exit;
			} else {
				include 'src/core/views/new_password.php';
				exit;
			}
		}

		if(!isset($_POST['email'])) {
			include 'src/core/views/reset_password.php';
			return;
		}

		$email = $_POST['email'];
		$out = [];

		$r = user::getByEmail($email);

		if ($r == false) {
  			echo "No user found with this email.";
  			$out['success'] = false;
  			$out['msg'] = "No user found with this email.";
  			exit;
		}

        $baseurl = gila::config('base');
		$subject = "Change Password Code for ".$r['username'];
		$reset_code = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),0,50);
        $message = "Hello {$r['username']}\n\n";
        $message .= "This is the link to change your password in $baseurl\n\n";
        $message .= $baseurl."login/password_reset?rp=$reset_code\n\n";
		$message .= "If you didn't ask to change the password please ignore this email";

		$headers = "From: GilaCMS <noreply@{$_SERVER['HTTP_HOST']}>";
		user::meta($r['id'],'reset_code',$reset_code);
		mail($email,$subject,$message,$headers);

		$out['success'] = true;
		$out['msg'] = "An email has been send to you in order to reset you password.";
		echo "OK";
	}
}
