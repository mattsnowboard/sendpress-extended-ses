<?php

// Prevent loading this file directly
if (!defined('SENDPRESS_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    die;
}

if (!class_exists('SendPress_Sender_Ses')) {

class SendPress_Sender_Ses extends SendPress_Sender
{
    public function label()
    {
        return __('SES','sendpress');
    }

    public function save()
    {
        
    }

    public function settings()
    {?>
        <p><?php _e( 'AWS SES', 'sendpress' ); ?>.</p>
        <p><?php _e('This is a customized option that uses Amazon SES to send email. It allows you to send much more mail but must have required plugins setup and working. <strong>Use this option</strong>','sendpress'); ?>.</p>
        <?php
        if (SendPress_Option::get('sendmethod') == 'SendPress_Sender_Ses'): ?>
        <div class="alert alert-success">
            <?php _e('<b>OKAY: </b>Sendpress is configured to use Amazon SES, no extra setup needed! Make sure that <a href="options-general.php?page=sendpress-extended/sendpress-extended.php">SES settings</a> are okay','sendpress'); ?>.
        </div>
        <?php endif;
    }


    public function send_email($to, $subject, $html, $text, $istest = false, $sid, $list_id, $report_id)
    {
        global $phpmailer, $wpdb;
        
        // Make sure we have the right $phpmailer
        if (!is_object( $phpmailer ) || !method_exists($phpmailer, 'IsSES')) {
            throw new Exception('Using $phpmailer that does not have SES support!');
        }
        
        /*
         * Make sure the mailer thingy is clean before we start,  should not
         * be necessary, but who knows what others are doing to our mailer
         */
        $phpmailer->ClearAddresses();
        $phpmailer->ClearAllRecipients();
        $phpmailer->ClearAttachments();
        $phpmailer->ClearBCCs();
        $phpmailer->ClearCCs();
        $phpmailer->ClearCustomHeaders();
        $phpmailer->ClearReplyTos();
        //return $email;
        
        $charset = SendPress_Option::get('email-charset','UTF-8');
        $encoding = SendPress_Option::get('email-encoding','8bit');

        $phpmailer->CharSet = $charset;
        $phpmailer->Encoding = $encoding;

        if($charset != 'UTF-8'){
             $html = $this->change($html,'UTF-8',$charset);
             $text = $this->change($text,'UTF-8',$charset);
             $subject = $this->change($subject,'UTF-8',$charset);
        }

        $subject = str_replace(array('’','“','�?','–'),array("'",'"','"','-'),$subject);
        $html = str_replace(chr(194),chr(32),$html);
        $text = str_replace(chr(194),chr(32),$text);

        
        $phpmailer->MsgHTML($html);
        $phpmailer->AddAddress(trim($to));
        $phpmailer->AltBody= $text;
        $phpmailer->Subject = $subject;
        $content_type = 'text/html';
        $phpmailer->ContentType = $content_type;
        // Set whether it's plaintext, depending on $content_type
        //if ( 'text/html' == $content_type )
        $phpmailer->IsHTML(true);
        
        /**
        * We'll let php init mess with the message body and headers.  But then
        * we stomp all over it.  Sorry, my plug-inis more important than yours :)
        */
        do_action_ref_array('phpmailer_init', array(&$phpmailer));
        
        $from_email = SendPress_Option::get('fromemail');
        $phpmailer->From = $from_email;
        $phpmailer->FromName = SendPress_Option::get('fromname');
        $phpmailer->Sender = SendPress_Option::get('fromemail');
        $sending_method  = SendPress_Option::get('sendmethod');
        
        $phpmailer->IsSES();
        // Set the other options
        
        // Set SMTPDebug to 2 will collect dialogue between us and the mail server
        if ($istest == true) {
            $phpmailer->SMTPDebug = 2;
            // Start output buffering to grab smtp output
            ob_start(); 
        }

        // Send!
        $result = true; // start with true, meaning no error
        $result = @$phpmailer->Send();

        //$phpmailer->SMTPClose();
        if($istest == true){
            // Grab the smtp debugging output
            $smtp_debug = ob_get_clean();
            SendPress_Option::set('phpmailer_error', $phpmailer->ErrorInfo);
            SendPress_Option::set('last_test_debug', $smtp_debug);
        }

        if ($result != true && $istest == true) {
            $hostmsg = 'host: '.($phpmailer->Host).'  port: '.($phpmailer->Port).'  secure: '.($phpmailer->SMTPSecure) .'  auth: '.($phpmailer->SMTPAuth).'  user: '.($phpmailer->Username)."  pass: *******\n";
            $msg = '';
            $msg .= __('The result was: ','sendpress').$result."\n";
            $msg .= __('The mailer error info: ','sendpress').$phpmailer->ErrorInfo."\n";
            $msg .= $hostmsg;
            $msg .= __("The SMTP debugging output is shown below:\n","sendpress");
            $msg .= $smtp_debug."\n";
        }
        
        return $result;
    }



}


}