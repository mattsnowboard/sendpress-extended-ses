<?php


// Prevent loading this file directly
if ( !defined('SENDPRESS_VERSION') ) {
	//header('HTTP/1.0 403 Forbidden');
	//die;
}


class SendPressExtended_View_Ses extends SendPress_View {
	
	function html($sp) {?>
<form method="post" id="post">

<br class="clear">
<div style="float:right;" >
	<a href="?page=sp-templates&view=account" class="btn btn-large" ><i class="icon-remove"></i> <?php _e('Cancel','sendpress'); ?></a> <a href="#" id="save-update" class="btn btn-primary btn-large"><i class="icon-white icon-ok"></i> <?php _e('Save','sendpress'); ?></a>
</div>
<input type="hidden" name="action" value="ses-setup" />
<br class="clear">
<div class="boxer form-box">
<div class="sendpress-panel-column-container">
<div class="sendpress-panel-column">
	<h4>
		<input name="sendmethod" type="checkbox"  <?php if(SendPress_Option::get('sendmethod') == 'ses' ) { ?>checked="checked"<?php } ?>   id="ses" value="ses" >
		<?php _e( 'Amazon SES','sendpress' ); ?>
	</h4>
	<p><?php _e('This is a customized option that uses Amazon SES to send email. It allows you to send much more mail but must have required plugins setup and working. <strong>Use this option</strong>','sendpress'); ?>.</p>
</div>
</div>

<?php if (SendPress_Option::get('sendmethod') == 'ses'): ?>
<div class="alert alert-success">
	<?php _e('<b>OKAY: </b>Sendpress is configured to use Amazon SES, no extra setup needed! Make sure that <a href="options-general.php?page=aws-ses-email/aws-ses-email.php">SES settings</a> are okay','sendpress'); ?>.
</div>
<?php endif; ?>

</div>
<?php wp_nonce_field($sp->_nonce_value); ?>
</form>
<form method="post" id="post" class="form-horizontal">
<input type="hidden" name="action" value="test-account-setup" />
<br class="clear">
<div class="alert alert-success">
	<?php _e('<b>NOTE: </b>Remeber to check your spam folder if you do not seem to be recieving emails','sendpress'); ?>.
</div>

<h3><?php _e('Send Test Email','sendpress'); ?></h3>
<input name="testemail" type="text" id="appendedInputButton" value="<?php echo SendPress_Option::get('testemail'); ?>" style="width:100%;" />
<button class="btn btn-primary" type="submit"><?php _e('Send Test!','sendpress'); ?></button><button class="btn" data-toggle="modal" data-target="#debugModal" type="button"><?php _e('Debug Info','sendpress'); ?></button>
<br class="clear">



<?php wp_nonce_field($sp->_nonce_value); ?>
</form>
<?php
$error= 	SendPress_Option::get('phpmailer_error');
$hide = 'hide';
if(!empty($error)){
	$hide = '';
	$phpmailer_error = '<pre>'.$error.'</pre>';
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#debugModal').modal('show');
	});
	</script>

	<?php
}


	}
    
    function prerender($sp){
		
	}

}

SendPressExtended_View_Ses::cap('sendpress_ses');