<?php

// Prevent loading this file directly
if (!defined('SENDPRESS_VERSION')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

require_once SENDPRESS_PATH . 'classes/class-sendpress-sender.php';

class SendPressExtended_Sender_SES
{

	function init()
    {
		add_filter('sendpress_sending_method_ses',array('SendPressExtended_Sender_SES','ses'),10,1);
	}

	function ses($phpmailer)
    {
		// Make sure we have the right $phpmailer
        if (!method_exists($phpmailer, 'IsSES')) {
            throw new Exception('Using $phpmailer that does not have SES support!');
        }
		$phpmailer->IsSES();
		error_log('aws-ses');
        
		return $phpmailer;
	}

}