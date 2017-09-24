<?php
	include('includes/header.php');
        $logger = new logger('manageUser');
	if(isset($_POST['update_user']))
	{
		//Handle user account changes
		//debug_print($_POST);
		
		//Get the user.
		$u = new user($logger);
		$u->users_id = $_POST['users_id'];
		$u->load_me();
		
		//Handle basic account changes.
		$u->users_first = $_POST['users_first'];
		$u->users_last = $_POST['users_last'];
		$u->users_email = $_POST['users_email'];
		$u->users_masq = $_POST['users_masq'];
		$u->update_me();
		
		divAlert("User Account Updated.",'success');
	}
?>

<script>
$(document).ready(function(e) {
	var loadUser = function(id) {
		$.ajax({
			url:'<?php echo getHostFQDN() . 'util/queryUserObject.php?uid='?>'+id,
			dataType:"json"
		}).done(function(data) {
			console.info(data);
			$("#userInfo").hide();
			$("#users_email").val(data.users_email);
			$("#users_first").val(data.users_first);
			$("#users_last").val(data.users_last);
			$("#users_id").val(data.users_id);
			$("#users_masq").val(data.users_masq);
			$("#show_user_id").html("User ID: " + data.users_id);
			if(data.users_active=='N')
			{
				$("#disableUser").hide();
				$("#enableUser").show();
			} else {
				$("#disableUser").show();
				$("#enableUser").hide();				
			}
			if(data.users_setup != 'Y')
			{
				$("#userInfoMessage").html("This user has not setup their account yet");
				$("#userInfo").show();
			}
		})
	};
	
    $("#userSearch").select2({
    	dropdownAutoWidth:'true',
		ajax:
		{
			url: "<?php echo getHostFQDN() . 'util/queryUser.php' ?>",
			dataType: 'json',
			data: function (user)
			{
				return { u:user };
			},
			results: function (data,page)
			{
				return {results: data};
			}
		}		
	}).on("change", function(e) { loadUser($("#userSearch").val()); });
	
	$("#resetpwd").click(function(e) {
		var uid = $("#users_id").val();
        $.ajax(
			{
				url: "<?php echo getHostFQDN() . 'util/sendPasswordReset.php?uid=' ?>"+uid
			}
		)
		alert('Password Reset Email Sent');
    });
	
	$("#disableUser").click(function(e) {
		var uid = $("#users_id").val();
        $.ajax(
			{
				url: "<?php echo getHostFQDN() . 'util/disableUser.php?action=disable&uid=' ?>"+uid
			}
		)
		$("#disableUser").hide();
		$("#enableUser").show();		
		alert('User has been disabled');
    });	
	
	$("#enableUser").click(function(e) {
		var uid = $("#users_id").val();
        $.ajax(
			{
				url: "<?php echo getHostFQDN() . 'util/disableUser.php?action=enable&uid=' ?>"+uid
			}
		)
		$("#disableUser").show();
		$("#enableUser").hide();		
		alert('User has been enabled');
    });	
	
	$("#sudotouser").click(function(e) {
        var uid = $("#users_id").val();
		window.location.href = "/util/sudo.php?uid="+uid;
    });
	
});

</script>
<h3>Manage User</h3>
<form action="" method="get" name="usersearch">
<div class="row">
	<div class="form-group col-md-8 col-sm-12 col-xs-12">
		<label for="userSearch" >Search Users</label>
		<input type="text" name="userSearch" id="userSearch" style="width:250px" />
	</div>
</div>  
</form>
<h3 id="show_user_id">User ID: </h3>
	<div id="userInfo" class="col-md-12" style="display:none">
    	<div id="userInfoMessage" class="alert alert-info"></div>
    </div>
	<div class="row">
    	<div class="col-md-8 col-sm-12 col-xs-12">
            <form action="" method="post" id="manageuser">
                <div class="form-group">
                    <label for="users_first">First Name</label>
                    <input name="users_first" type="text" class="form-control" id="users_first" />
                </div>
                
                <div class="form-group">  
                  <label for="users_last">Last Name</label>
                    <input name="users_last" type="text" class="form-control" id="users_last" />
                </div>
            
                <div class="form-group">    
                  <label for="users_email">Email Address</label>
                    <input name="users_email" type="text" class="form-control" id="users_email" />
                </div>
                <div class="form-group">    
                  <label for="users_email">Masquerade Email Address</label>
                    <input name="users_masq" type="text" class="form-control" id="users_masq" />
                </div>                
                <input type="hidden" value="1" name="update_user" id="update_user" />
                <input type="hidden" value="" id="users_id" name="users_id" />
		</div>
        <div class="col-md-4 col-sm-4 col-xs-12 push" style="margin-top:25px">
			<button class="btn btn-block btn-primary">Save Changes</button>
            </form>            
			<button class="btn btn-block btn-primary" id="resetpwd" name="resetpwd" >Password Reset</button>
            <button class="btn btn-block btn-primary" id="sudotouser" name="sudotouser" >Sudo to User</button>
            <button class="btn btn-block btn-danger" style="margin-top:25px; display:none" id="disableUser" name="disableUser">Disable User</button>
			<button class="btn btn-block btn-danger" style="margin-top:25px; display:none" id="enableUser" name="enableUser">Enable User</button>            
		</div>
	</div>
<?php
	include('includes/footer.php');
?>
