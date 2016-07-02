<?php
if(isset($_POST['name'])) {
	include('includes/header.php');
	$logger = new logger('pass-reset');
	$admin = new user($logger);
	$admin->users_id = 1;
	$admin->load_me();
	if(isset($_POST['user']))
	{
		$u = new user($logger);
		$u->users_email = $_POST['user'];
		if($u->loadFromEmail())
		{
			debug_print($u);
			$u->users_setup = 'N';
			$u->users_nonce = md5(time());
			$u->update_me();
			$u->sendPasswordResetEmail($admin);
		} else {
			echo '<div class="alert alert-error">No account with that email exists.</div>';
		}
	}
}
?>
<head>
	<link rel="stylesheet" type="text/css" href="css/signin.css"/>
</head>


<div class="container">

      <form class="form-signin" role="form" method="post">
        <h3 class="form-signin-heading">Enter Your Email Address</h3>
        <input type="text" class="form-control" name="user" id="user" placeholder="Email address" required autofocus>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Reset Password</button>
      </form>
    </div> <!-- /container --><strong></strong>