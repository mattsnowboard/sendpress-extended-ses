<?php

require_once ABSPATH . WPINC . '/class-phpmailer.php';

/**
 * Extend the PHPMailer class to add SES support
 */
class AWSMailer extends PHPMailer
{
    protected $ses = null;
    
    protected $awsSesOptions = array();
    
    protected $awsSesResponse = null;
    
    protected $sesMaxRate = 0;
    
    protected $lastSesSend = 0;
    
    protected $debugThrottleCount = 0;

    public function __construct($exceptions = false)
    {
        parent::__construct($exceptions);
    }

    public function SetSES($ses)
    {
        $this->ses = $ses;
        $this->awsSesOptions = get_option('aws_ses_email_options');
        $quota = $this->ses->getSendQuota();
        // how fast in seconds we can send
        $this->sesMaxRate = ($quota['MaxSendRate'] != 0) ? 1 / $quota['MaxSendRate'] : 1;
    }

    /**
     * Sets Mailer to send message using SES API.
     * @return void
     */
    public function IsSES()
    {
        $this->Mailer = 'ses';
    }

    public function GetLastSESResponse()
    {
        return $this->awsSesResponse;
    }
    
    /**
     * Override this to support SES
     * @return boolean
     * @throws phpmailerException
     */
    public function PostSend()
    {
        try {
            if ($this->Mailer == 'ses') {
                return $this->SESSend($this->MIMEHeader, $this->MIMEBody);
            } else {
                return parent::PostSend();
            }
        } catch (phpmailerException $e) {
            $this->SetError($e->getMessage());
            if ($this->exceptions) {
                throw $e;
            }
            if ($this->SMTPDebug) {
                echo $e->getMessage() . "\n";
            }
            return false;
        }
    }

    /**
     * Sends mail using the SES API.
     * @param string $header The message headers
     * @param string $body The message body
     * @access protected
     * @return bool
     */
    protected function SESSend($header, $body)
    {
        if (is_null($this->ses)) {
            throw new phpmailerException('AWS SES service was not set on mailer object', self::STOP_CRITICAL);
        }
        
        
        /*$html = $body;
        $txt = strip_tags($html);
        if (strlen($html) == strlen($txt)) {
            $html = '';
        }*/
        $m = new SimpleEmailServiceMessage();
        foreach ($this->to as $to) {
            $m->addTo($to[0]);
        }
        foreach ($this->cc as $cc) {
            $m->addCC($cc[0]);
        }
        foreach ($this->bcc as $bcc) {
            $m->addBCC($bcc[0]);
        }
        
        $m->setFrom('"' . $this->FromName . '" <' . $this->From . ">");
        $m->setReturnPath($this->awsSesOptions['return_path']);
        $m->setSubject($this->Subject);
        
        $m->setMessageFromString($this->AltBody, $this->Body);
        /*if ($html == '') {
            $m->setMessageFromString($body);
        } else {
            $m->setMessageFromString($txt, $html);
        }*/
        
        try {
            // throttling (only works per instance, i.e. this is broken if multiple instances are sending email)
            $time = microtime(true);
            if (($time - $this->lastSesSend) < $this->sesMaxRate) {
                // just sleep the max rate converted to number of microseconds
                usleep($this->sesMaxRate * 1000000);
                $this->debugThrottleCount++;
            }
            
            $this->awsSesResponse = $this->ses->sendEmail($m);
            $this->lastSesSend = microtime(true);
            if (is_array($this->awsSesResponse)) {
                // check for errors
                return true;
            } else {
                return false;
            }
        } catch (phpmailerException $e) {
            
        }
    }

}