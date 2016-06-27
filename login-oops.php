<?php 
  /**
   * Logs a user out and prompts for re-login when their cookie is not valid.
   *
   * This page assumes that if the secure cookie stored in a client's browser
   * is not current / valid, that they have already signed in somewhere else,
   * and thereofre this cookie should be invalidated.
   *
   * @author Michael Munger <michael@highpoweredhelp.com>
   **/    

	setcookie("current_user","",time()-3600);
	setcookie("mobile_user","",time()-3600);
?>
<!DOCTYPE html>
<head>
<link rel="stylesheet" type="text/css" href="css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/signin.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="container">
    <div class="alert alert-danger text-center">
      You've been logged out because someone else logged in as you
    </div>
    <p><img src="img/facepalm.png" class="img-responsive col-md-6">
    <strong>Because the laws of space time prevent you from being in two places at once, you cannot log into this system from two different locations with the same credentials.</strong></p>
    <p>Your initial session was <strong>invalidated </strong>when a second session was started with your same username and password. This could be because you have shared your credentials with someone else, or it could be because you logged in from a different browser or different computer without logging out on this one. You'll have to re-login using the link below.</p>
    <h3 class="text-center"><a href="login.php?return=/" >Click Here to Re-Login</a></h3>
    <p>If you need additional accounts for members of your team, please <a href="mailto:michael@highpoweredhelp.com">contact support</a> and request more accounts to avoid this error in the future.</p>
    </p>
</div>
</body>