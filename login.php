<?php 
$rootDir = __DIR__;

//If a username has not been submitted, dont' attempt to authorize this person. If it has, authorize them, which should redirect them to their dashboard.
echo "<pre>"; var_dump($_POST); echo "</pre>";
$NOAUTH=!isset($_POST['user']);
echo "<pre>"; var_dump($NOAUTH); echo "</pre>";

include('includes/bootstrap.php');

$Engine->runActions('include-header');
?>

<div class="container">
  <p class="text-center"><img src="<?php $Engine->runActions('get-site-logo'); ?>" /> </p>
  <form class="form-signin" role="form" method="post">
    <h2 class="form-signin-heading text-center">Please sign in</h2>
    <input type="text" class="form-control" name="username" id="username" placeholder="Email address" required autofocus>
    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
    <label class="checkbox">
      <input type="checkbox" value="remember-me" name="remember" id="remember" checked> Remember me
    </label>
    <button class="btn btn-lg btn-andretti btn-block" type="submit">Sign in</button>
  </form>
  <p align="center"><a href="reset.php">Forgot Your Password?</a></p>
</div> <!-- /container -->
