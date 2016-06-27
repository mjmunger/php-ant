<?php
/**
 * Creates a user with the information in the 
 *
 * @package      BFW Toolkit
 * @subpackage   Core
 * @category     User Creation
 * @author       Michael Munger <michael@highpoweredhelp.com>
 * @todo         Create plugins for the user-created-success hook to send a welcome email.
 * @todo         Modify this page to use an Html5Select() Class to display roles from the database.
 * @todo         Refactor this into the default user plugin.
 */ 

include('includes/header.php');

$logger = new logger('createUser');

//Add user
if(isset($_POST['createuser'])) {
    //Let's add a user to the db!
    $u = new user($logger);
    $u->users_first = trim($_POST['first']);
    $u->users_last = trim($_POST['last']);
    $u->users_email = trim($_POST['email1']);
    $u->users_role = trim($_POST['role']);
    $u->users_nonce = md5(time());
    $id = $u->insert_me();
    if($id) {
        run_action('user-created-success',array('user'=>$u));
    } else {
        run_action('user-created-failed',array('user'=>$u));
    }
    divAlert('User Created.','success');
}
?>

<script type="text/javascript">
$('#createUser').validate();
$(document).ready(function(e) {
    $('#email2').blur(function() {
        if($('#email1').val() != $('#email2').val()) {
            alert('Emails must match.');
            $("#email2").focus();
        }
    });    
}); 
</script>
<h2>Create User </h2>
<div class="col-md-9">
    <form action="" method="post" name="createuser" id="createUser" role="form" class="form-horizontal">
        <div class="form-group">
          <label for="first">First Name</label>
          <input type="text" name="first" id="first" class="form-control" placeholder="Enter User's First Name" minlength="2" required/>
        </div>
        <div class="form-group">
            <label for="last">Last Name</label>
            <input type="text" name="last" id="last" class="form-control" placeholder="Enter User's Last Name" minlength="2" required/>
        </div>
        <div class="form-group">
            <label for="email1">Email Address</label>
            <input type="text" name="email1" id="email1" class="form-control" placeholder="Enter Email Address" type="email" required/>
        </div>
        <div class="form-group">
            <label for="email2">Confirm Email</label>
            <input type="text" name="email2" id="email2" class="form-control" placeholder="Confirm Email Address" type="email" required/>
        </div>
        <div class="form-group">
            <label for="role">Role
                <select name="role" id="role"required>
                    <option value="" selected>Select Role</option>
                    <?php
                        $roles = getRoles();
                        foreach($roles as $role => $id) {
                            printf('<option value="%s">%s</option>',$id,$role);
                        }
                    ?>
                </select>
            </label>
        </div>
        <button class="btn" type="submit">Create User </button>
        <input type="hidden" name="createuser" value="1"/>
    </form>
</div>
<?php
    include('includes/footer.php');
?>