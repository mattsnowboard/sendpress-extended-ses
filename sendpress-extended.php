<?php

/*
  Plugin Name: SendPress Extended: Email Marketing and Newsletters
  Version: 0.9
  Plugin URI: 
  Description: Extending SendPress plugin (which must be active and working) with features to use SES
  Author: Matt Durak
  Depends: SendPress: Email Marketing and Newsletters
 */

require_once 'AWSMailer.php';
require_once 'ses.php';

if (!defined('SENDPRESS_PATH')) {
    //die;
}

define( 'SENDPRESS_EXTENDED_PATH', plugin_dir_path(__FILE__) );

class SendPressExtended
{

    public function init()
    {
        sendpress_register_sender('SendPress_Sender_Ses');
    }
    
    public static function autoload($className)
    {
        if (strpos($className, 'SendPress') !== 0) {
            return;
        }

        $cls = str_replace('_', '-', strtolower($className));
        if (substr($cls, -1) == '-') {
            $cls = substr($cls, 0, -1);
            $className = substr($className, 0, -1);
        }
        if (class_exists($className)) {
            return;
        }

        if (strpos($className, 'Public_View') != false) {
            $file = SENDPRESS_EXTENDED_PATH . "classes/public-views/class-" . $cls . ".php";
            if (file_exists($file)) {
                include $file;
                return;
            }
        }

        if (strpos($className, 'View') != false) {
            $file = SENDPRESS_EXTENDED_PATH . "classes/views/class-" . $cls . ".php";
            if (file_exists($file)) {
                include $file;
                return;
            }
        }

        if (strpos($className, 'Module') != false) {
            $file = SENDPRESS_EXTENDED_PATH . "classes/modules/class-" . $cls . ".php";
            if (file_exists($file)) {
                include $file;
                return;
            }
        }
        
        $file = SENDPRESS_EXTENDED_PATH . "classes/class-" . $cls . ".php";
        if (file_exists($file)) {
            include $file;
            return;
        }
    }

}

function sendpress_extended_init()
{
    $sendpressExtended = new SendPressExtended();
    $sendpressExtended->init();
}

spl_autoload_register(array('SendPressExtended', 'autoload'));

add_action('sendpress_init', 'sendpress_extended_init');

// AWS SES STUFF
if (is_admin()) {
    add_action('admin_menu', 'aws_ses_email_admin_menu');
    register_activation_hook(__FILE__, 'aws_ses_email_install');
    register_deactivation_hook(__FILE__, 'aws_ses_email_uninstall');
}

function aws_ses_email_install()
{
    global $aws_ses_email_options;
    if (!get_option('aws_ses_email_options')) {
        add_option('aws_ses_email_options',
            array(
                'from_email' => '',
                'return_path' => '',
                'from_name' => '',
                'access_key' => '',
                'secret_key' => '',
                'credentials_ok' => 0,
                'sender_ok' => 0,
                'last_ses_check' => 0, // timestamp of last quota check
                'active' => 0, // reset to 0 if not pluggable or config change.
                'version' => '0'
            )
        );
    }
    $aws_ses_email_options = get_option('aws_ses_email_options');
}

function aws_ses_email_uninstall()
{
    delete_option('aws_ses_email_options');
}

function aws_ses_email_admin_menu()
{
    add_options_page('aws_ses_email', __('AWS SES Email', 'aws_ses_email'), 'manage_options', __FILE__, 'aws_ses_email_options');
    // Quota and Stats
    add_submenu_page('index.php', 'SES Stats', 'SES Stats', 'manage_options', 'sendpress-extended/ses-stats.php');
}

function aws_ses_email_options()
{
    global $wpdb, $aws_ses_email_options;
    
    $authorized = array();
    if (isset($aws_ses_email_options['access_key']) &&
        $aws_ses_email_options['access_key'] != '' &&
        isset($aws_ses_email_options['secret_key']) &&
        $aws_ses_email_options['secret_key'] != '') {
        $authorized = aws_ses_email_getverified();
    }
    $senders = (array)get_option('aws_ses_email_senders');
    $sender_domains = (array)get_option('aws_ses_email_sender_domains');
    // Update the authorized senders list
    $update_senders = false;
    $update_sender_domains = false;
    if (isset($authorized['Addresses'])) {
        foreach ($authorized['Addresses'] as $email) {
            if (!array_key_exists($email, $senders)) {
                $senders[$email] = array(
                    -1,
                    true
                );
                $update_senders = true;
            } else {
                if (!$senders[$email][1]) {
                    $senders[$email][1] = true;
                    $update_senders = true;
                }
            }
        }
        if ($update_senders) {
            update_option('aws_ses_email_senders', $senders);
        }
    }
    if (isset($authorized['Domains'])) {
        foreach ($authorized['Domains'] as $domain) {
            if (!array_key_exists($domain, $sender_domains)) {
                $sender_domains[$domain] = array(
                    -1,
                    true
                );
                $update_sender_domains = true;
            } else {
                if (!$sender_domains[$domain][1]) {
                    $sender_domains[$domain][1] = true;
                    $update_sender_domains = true;
                }
            }
        }
        if ($update_sender_domains) {
            update_option('aws_ses_email_sender_domains', $sender_domains);
        }
    }
    $update_options = false;
    $sendpress_active = class_exists('SendPress_Option');

    if (!isset($aws_ses_email_options['sender_ok'])
        || ($aws_ses_email_options['sender_ok'] != 1)
        || (!isset($aws_ses_email_options['credentials_ok'])
        || $aws_ses_email_options['credentials_ok'] != 1)) {
        $aws_ses_email_options['active'] = 0;
        update_option('aws_ses_email_options', $aws_ses_email_options);
    }
    $from_domain = (isset($aws_ses_emails_options['from_email']))
        ? substr($aws_ses_email_options['from_email'], strpos($aws_ses_email_options['from_email'], '@') + 1)
        : '';
    if (isset($aws_ses_email_options['from_email'])
        && ($aws_ses_email_options['from_email'] != '')
        && ((isset($senders[$aws_ses_email_options['from_email']])
             && $senders[$aws_ses_email_options['from_email']][1] === TRUE)
            || (isset($sender_domains[$from_domain]) && $sender_domains[$from_domain][1] === TRUE))) {
        if ($aws_ses_email_options['credentials_ok'] == 0) {
            $aws_ses_email_options['credentials_ok'] = 1;
            $update_options = true;
        }
        if ($aws_ses_email_options['sender_ok'] == 0) {
            $aws_ses_email_options['sender_ok'] = 1;
            $update_options = true;
        }
    }
    if (isset($aws_ses_email_options['from_email'])
        && ((isset($senders[$aws_ses_email_options['from_email']]) &&
            $senders[$aws_ses_email_options['from_email']][1] !== TRUE)
            || (isset($sender_domains[$from_domain]) &&
                $sender_domains[$from_domain][1] !== TRUE))) {
        $aws_ses_email_options['sender_ok'] = 0;
        $update_options = true;
    }

    if (!empty($_POST['activate'])) {
        if (($aws_ses_email_options['sender_ok'] == 1) && ($aws_ses_email_options['credentials_ok'] == 1)) {
            $aws_ses_email_options['active'] = 1;
            $update_options = true;
            echo '<div id="message" class="updated fade">
                            <p>' . __('Plugin is activated and functionnal',
                                      'aws_ses_email') . '</p>
                            </div>' . "\n";
        }
    }
    if (!empty($_POST['deactivate'])) {
        $aws_ses_email_options['active'] = 0;
        $update_options = true;
        echo '<div id="message" class="updated fade">
                            <p>' . __('Plugin de-activated',
                                      'aws_ses_email') . '</p>
                            </div>' . "\n";
    }
    if (!empty($_POST['save'])) {
        if (!isset($aws_ses_email_options['from_email'])
            || $aws_ses_email_options['from_email'] != trim($_POST['from_email'])) {
            $aws_ses_email_options['sender_ok'] = 0;
            $aws_ses_email_options['active'] = 0;
        }
        $aws_ses_email_options['from_email'] = trim($_POST['from_email']);
        $aws_ses_email_options['return_path'] = trim($_POST['return_path']);
        if ($aws_ses_email_options['return_path'] == '') {
            $aws_ses_email_options['return_path'] = $aws_ses_email_options['from_email'];
        }
        $aws_ses_email_options['from_name'] = trim($_POST['from_name']);
        
        if ($sendpress_active) {
            SendPress_Option::set('fromemail', $_POST['from_email']);
            SendPress_Option::set('fromname', $_POST['from_name']);
        }

        if (!isset($aws_ses_email_options['access_key'])
            || ($aws_ses_email_options['access_key'] != trim($_POST['access_key']))
            || (!isset($aws_ses_email_options['secret_key'])
                || $aws_ses_email_options['secret_key'] != trim($_POST['secret_key']))) {
            $aws_ses_email_options['credentials_ok'] = 0;
            $aws_ses_email_options['sender_ok'] = 0;
            $aws_ses_email_options['active'] = 0;
        }
        $aws_ses_email_options['access_key'] = trim($_POST['access_key']);
        $aws_ses_email_options['secret_key'] = trim($_POST['secret_key']);

        $update_options = true;
        echo '<div id="message" class="updated fade"><p>' . __('Settings updated',
                                                               'aws_ses_email') . '</p> </div>' . "\n";
    }

    if ($update_options) {
        update_option('aws_ses_email_options', $aws_ses_email_options);
    }

    $aws_ses_email_options = get_option('aws_ses_email_options');

    if (!empty($_POST['addemail'])) {
        aws_ses_email_verify_sender_step1($aws_ses_email_options['from_email']);
    }

    if (!empty($_POST['testemail'])) {
        aws_ses_email_test_email($aws_ses_email_options['from_email']);
    }

    if (!empty($_POST['prodemail'])) {
        aws_ses_email_prod_email($_POST['prod_email_to'],
                                 $_POST['prod_email_subject'],
                                 $_POST['prod_email_content']);
    }

    include ('admin.tmpl.php');
}

function aws_ses_email_getverified()
{
    global $SES;
    aws_ses_email_check_SES();
    $result = $SES->listIdentities();
    if (is_array($result)) {
        return $result;
    } else {
        return array();
    }
}

function aws_ses_email_verify_sender_step1($mail)
{
    global $aws_ses_email_options;
    global $SES, $AWSSESMSG;
    aws_ses_email_check_SES();
    $AWSSESMSG = '';
    try {
        $rid = $SES->verifyEmailAddress($mail);
        $senders = get_option('aws_ses_email_senders');
        if ($rid != '') {
            $senders[$mail] = array(
                $rid['RequestId'],
                false
            );
            $aws_ses_email_options['credentials_ok'] = 1;
            update_option('aws_ses_email_options', $aws_ses_email_options);
            update_option('aws_ses_email_senders', $senders);
        }
    } catch (Exception $e) {
        $AWSSESMSG = __('Got exception: ', 'aws_ses_email') . $e->getMessage() . "\n";
    }
    $AWSSESMSG .= ' id ' . var_export($rid, true);
    aws_ses_email_message_step1done();
}

function aws_ses_email_message_testdone()
{
    global $AWSSESMSG;
    echo "<div id='wpses-warning' class='updated fade'><p><strong>" . __("Test message has been sent to your sender Email address.<br />SES Answer - ",
                                                                         'aws_ses_email') . $AWSSESMSG . "</strong></p></div>";
}

function aws_ses_email_test_email()
{
    global $aws_ses_email_options;
    global $SES, $AWSSESMSG;
    aws_ses_email_check_SES();
    $AWSSESMSG = '';
    $rid = aws_ses_mail($aws_ses_email_options['from_email'],
                      __('AWS SES - Test Message', 'aws_ses_email'),
                      __("This is AWS SES Test message. It has been sent via Amazon SES Service.\nAll looks fine !\n\n", 'aws_ses_email'));
    $AWSSESMSG .= ' id ' . var_export($rid, true);
    aws_ses_email_message_testdone();
}

function aws_ses_email_prod_email($mail, $subject, $content)
{
    global $aws_ses_email_options;
    global $SES, $AWSSESMSG;
    aws_ses_email_check_SES();
    $AWSSESMSG = '';
    $rid = aws_ses_mail($mail, $subject, $content);
    $AWSSESMSG .= ' id ' . var_export($rid, true);
    echo "<div id='awsses-warning' class='updated fade'><p><strong>" . __("Test message has been sent.<br />SES Answer - ",
                                                                         'aws_ses_email') . $AWSSESMSG . "</strong></p></div>";
}

function aws_ses_email_check_SES()
{
    global $aws_ses_email_options;
    global $SES;
    if (!isset($SES)) {
        $SES = new SimpleEmailService($aws_ses_email_options['access_key'], $aws_ses_email_options['secret_key']);
    }
}

// TODO: use my AWSMailer class
function aws_ses_mail($to, $subject, $message, $headers = '') {
    global $SES;
    global $aws_ses_email_options;
    aws_ses_email_check_SES();
    $html = $message;
    $txt = strip_tags($html);
    if (strlen($html) == strlen($txt)) {
        $html = '';
    }
    $m = new SimpleEmailServiceMessage();
    $m->addTo($to);
    $m->setFrom('"' . $aws_ses_email_options['from_name'] . '" <' . $aws_ses_email_options['from_email'] . ">");
    $m->setReturnPath($aws_ses_email_options['return_path']);
    $m->setSubject($subject);
    if ($html == '') {
        $m->setMessageFromString($message);
    } else {
        $m->setMessageFromString($txt, $html);
    }
    $res = $SES->sendEmail($m);
    if (is_array($res)) {
        return $res['MessageId'];
    } else {
        return NULL;
    }
}

if (!isset($aws_ses_email_options)) {
    $aws_ses_email_options = get_option('aws_ses_email_options');
}

if ($aws_ses_email_options['active'] == 1) {
    $SES = new SimpleEmailService($aws_ses_email_options['access_key'], $aws_ses_email_options['secret_key']);
    $phpmailer = new AWSMailer(true);
    $phpmailer->SetSES($SES);
}

$AWSSESMSG = '';