<?php
	include('includes/header.php');
	$logger = new logger('mobile-project');
?>
<script type="text/javascript">
$(document).ready(function(e){
	$("#showResolvedBugs").click(function(e){
		$.ajax({
			url:'<?php echo getHostFQDN()."modules/showProjectResolvedBugs.php?mobile=1&pid=".$_GET['pid']; ?>'
		})
			.done(function(data){
				$("#resolvedBugs").html(data);
				$("#resolvedBugs").slideDown('fast');
			});
	});
});
</script>
<?php

	//Load the project
	$p = new project($logger,$_GET['pid']);
	if(isset($_GET['orderby']))
	{
		$p->set_order_by($_GET['orderby'],$_GET['sort']);
		$p->bugs = '';
		$p->load_bugs();
	}
	//Verify the current user has permissions to view the project.	
	if(!$p->verifyUserAccess($current_user))
	{
		divAlert('You do not have permissions to access this project. If you feel this was in error, <a href="mailto:michael@highpoweredhelp.com">email support</a>.','error');
		die("");
	}

/*	
	DEPRECATED. This just makes a mess.

	if($current_user->isWorker())
	{
		//Display project resources
		?>
        <h3>Project Resources</h3>
        <div id="files" class="files">
        	<?php $p->print_attachments(false); ?>
        </div>         
        <?php
	}*/
?>

<!--App version control starts here -->
<?php if($p->projects_is_app == 'Y') { ?>
	<div class="panel panel-success">
		<div class="panel-heading">
			App Downloads
		</div>
		<div class="panel-body">
			<div class="row col-lg-9 col-md-9 col-sm-9 col-xs-9">
				<p><strong>Current Version: <?php echo $p->getCurrentAppRevision(); ?></strong></p>
				<p>Published: <?php echo $p->getCurrentAppRevisionDate(); ?> </p>
				<div class="small">
					<?php echo $p->getCurrentAppNotes(); ?>
				</div>
			</div>
			<div class="row col-lg-3 col-md-3 col-sm-3 col-xs-3">
				<a href="<?php echo $p->getCurrentAppPath(); ?>"><img src="/img/download-icon.png"></a>
			</div>
		</div>
	</div>
<?php } ?> 

<a href="<?php echo $p->getReportBugLink(); ?>" class="btn btn-primary btn-block">Report Bug for this Project</a>

<h3>Open Bugs</h3>

<?php
if(getProjectBugCount($_GET['pid'])==0)
{
	?> <div class="alert alert-info text-center">There are no open bugs or tickets for this project.</div><?php
}
	foreach($p->bugs as $x)
	{
		if($x->bugs_status < 60)
			$x->printMobileBugCard();
	}
?>
</table>
<?php
if(getProjectBugCount($_GET['pid'],60)>0)
{
?>
<h3>Resolved Bugs</h3>
<button id="showResolvedBugs" name="showResolvedBugs">Show Resolved Bugs</button>
<div id="resolvedBugs" style="display:none">
</div>
<?php } ?>
<?php
	include('includes/footer.php');
?>

