<?php
/**
 * Account activation page
 *
 * This page allows a user of the system to activate their accounts by setting
 * a password. It also checks the password strength as well as its
 * crackability by talking to https://bugzy.highpoweredhelp.com/ to see if the
 * password is part of the common password lists. If it is an easily crackable
 * or leaked password, it will notify the user, and give them the option to
 * either: 1) change their password to something better, or 2) continue using
 * that password without any expectation or privacy or security.
 *
 * @package      BFW Toolkit
 * @subpackage   Core
 * @category     User Accounts
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 

//include('includes/application_top.php');

$logger = new logger('activate');
$u = new user($PE->Configs->pdo, $logger);
if(isset($_POST['id'])) {
	$u->users_id = $_POST['id'];
	$u->load_me();
	$u->createHash($_POST['password1']);
	$u->users_setup = 'Y';
	$u->users_active = 'Y';
	$u->users_nonce = '';
	$u->update_me();
    $configs = $PE->Configs->getConfigs(['siteURL']);
	header('location: ' . $configs['siteURL'] );
}

$u->users_nonce = filter_var($_GET['n'],FILTER_SANITIZE_STRING);
if($u->loadFromActivation()):

?>
<script type="text/javascript" src="js/jquery-1.11.0.min.js" language="javascript"></script>
<script type="text/javascript" src="js/bootstrap.min.js" language="javascript"></script>
<script type="text/javascript" src="js/pwstrength.js" language="javascript"></script>

<link rel="stylesheet" type="text/css" href="css/bootstrap-theme.min.css"/>
<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="css/signin.css">
<h1 align="center">Please create a password to activate your account.</h1>
<form name="createpasswd" id="createpasswd" role="form" class="form-signin" method="post">
<input type="password" name="password1" id="password1" class="form-control" placeholder="Password" required>
<div class="pwstrength_viewport_progress"></div>
<input type="password" name="password2" id="password2" class="form-control" placeholder="Confirm Password" required>
<input type="hidden" name="id" id="id" value="<?php echo $u->users_id; ?>" />
<button class="btn btn-lg btn-primary btn-block" type="submit" name="activateButton" id="activateButton">Activate Account</button>
</form>
<!-- Modal -->
<div class="modal fade" id="modalWarning" tabindex="-1" role="dialog" aria-labelledby="warningLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Password Warning</h4>
      </div>
      <div class="modal-body">
        <p>The password you have chosen is one of the most common, and easily crackable passwords on the internet.<p><p class="text-center"><strong><span class="text-center">It can be cracked in mere seconds by a novice attacker.</strong></span></p><p>In light of the fact that this website <strong>will</strong> contain usernames and passwords to your website, databases, and even (potentially) financial information, we <strong>strongly</strong> recommend you do not use this password. </p><p> If you choose to use this password on the site, you hereby reliquish any and all liability for information privacy and protection on this website. Do you really want to use this password?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Use Password Anyway</button>
        <button type="button" id="resetPasswords" class="btn btn-primary" data-dismiss="modal">Use a Different Password</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$(document).ready(function(e) {
	"use strict";
	var options = {};
	options.ui = {
		container: "#pwd-container",
		showVerdictsInsideProgressBar: true,
		viewports: {
			progress: ".pwstrength_viewport_progress"
		}
	};
	options.common = {
		debug: true,
		onLoad: function () {
			$('#messages').text('Start typing password');
		}
	};

	$("#modalWarning").on('shown.bs.modal',function(e){
		$("#resetPasswords").click(function(){
			$("#modalWarning").hide();
			$("#password2").val("");
			$("#password1").val("");
			$("#password1").focus();
		});		
	});
	$("#password1").pwstrength(options);
	$("#password2").keyup(function(){
		if($("#password1").val().length == $("#password2").val().length)
		{
			$.ajax({
				url:"https://bugzy.com/util/badPassLookup.php?p="+$("#password1").val()
			}).done(function (result){
				if(result == "FAIL")
				{
					$("#modalWarning").modal('show');
				}
			});
		}
	});
	
    $("#password2").blur(function() {
		if($('#password1').val() != $('#password2').val())
		{
			alert("Passwords do not match. Please try again.");
			$("#password2").val("");
			$("#password1").val("");
			$("#password1").focus();
		}
	});
});
</script>
<?php else: ?>
<?php
	/* Run the hook that will handle "unable to activate" problems. */
    $PE->runActions('unable-to-activate');
?>
<?php endif; ?>
<?php include('includes/footer.php'); ?>