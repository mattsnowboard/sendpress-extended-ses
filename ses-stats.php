<?php

if (!isset($aws_ses_email_options)) {
    $aws_ses_email_options = get_option('aws_ses_email_options');
}

if ($aws_ses_email_options['credentials_ok'] != 1) {
    $AWSSESMSG = __('Amazon API credentials have not been checked.<br />Please go to settings -> AWS SES Email and setup the plugin',
                    'aws_ses_email');
    include ('error.tmpl.php');
}

aws_ses_email_check_SES();

if (!is_object($SES)) {
    $AWSSESMSG = __('Error initializing SES. Please check your settings.',
                    'aws_ses_email');
    include ('error.tmpl.php');
}

$quota = $SES->getSendQuota();
$quota['SendRemaining'] = $quota['Max24HourSend'] - $quota['SentLast24Hours'];
if ($quota['Max24HourSend'] > 0) {
    $quota['SendUsage'] = round($quota['SentLast24Hours'] * 100 / $quota['Max24HourSend']);
} else {
    $quota['SendUsage'] = 0;
}

$stats = $SES->getSendStatistics();


include ('stats.tmpl.php');
