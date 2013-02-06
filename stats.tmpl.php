<div class="wrap">
  <div id="icon-options-general" class="icon32"><br /></div>
  <h2><?php _e('Amazon SES Stats','aws_ses_email') ?></h2>


  <h3><?php _e('Sending limits','aws_ses_email') ?></h3>

<table>
<tr><td><?php _e('Max24HourSend','aws_ses_email') ?>&nbsp;</td><td><?php echo $quota['Max24HourSend']?></td><td>&nbsp;<i><?php _e('Max email quota for 24 hours period','aws_ses_email') ?></i></td></tr>
<tr><td><?php _e('MaxSendRate','aws_ses_email') ?>&nbsp;</td><td><?php echo $quota['MaxSendRate']?> /s</td><td>&nbsp;<i><?php _e('Max email sending rate par second','aws_ses_email') ?></i></td></tr>
<tr><td><?php _e('SentLast24Hours','aws_ses_email') ?>&nbsp;</td><td><?php echo $quota['SentLast24Hours']?></td><td>&nbsp;<i><?php _e('Emails sent for the last 24 hours','aws_ses_email') ?></i></td></tr>
<tr><td><?php _e('SendRemaining','aws_ses_email') ?>&nbsp;</td><td><?php echo $quota['SendRemaining']?></td><td>&nbsp;<i><?php _e('Email sending quota remaining','aws_ses_email') ?></i></td></tr>
<tr><td><?php _e('SendUsage','aws_ses_email') ?>&nbsp;</td><td><?php echo $quota['SendUsage']?> %</td><td>&nbsp;<i><?php _e('Usage percentage per 24h','aws_ses_email') ?></i></td></tr>
</table>

  <br />&nbsp;
  <h3><?php _e('Sending Stats','aws_ses_email') ?></h3>
  <?php _e('Last 15 days of email statistics','aws_ses_email') ?>
  <br />
  <?php _e('Each line contains statistics for a 15 minutes period of sending activity. <br />Periods without any activity are not shown','aws_ses_email') ?>
  <br />&nbsp;
  
<table cellpadding="2">
<tr style="background-color:#ccc">
<td><?php _e('Timestamp','aws_ses_email') ?>&nbsp;</td>
<td><?php _e('DeliveryAttempts','aws_ses_email') ?>&nbsp;</td>
<td><?php _e('Bounces','aws_ses_email') ?>&nbsp;</td>
<td><?php _e('Complaints','aws_ses_email') ?>&nbsp;</td>
<td><?php _e('Rejects','aws_ses_email') ?>&nbsp;</td>
<td><?php _e('Total Ok','aws_ses_email') ?>&nbsp;</td>
<td><?php _e('Total Errors','aws_ses_email') ?>&nbsp;</td>
</tr>
<?php
    $i=1;
    $points = $stats['SendDataPoints'];
    $okay = uasort($points,
        function($a, $b) {
            $aDT = new \DateTime($a['Timestamp']);
            $bDT = new \DateTime($b['Timestamp']);
            return $bDT->getTimestamp() - $aDT->getTimestamp();
    });
    if (!$okay) {
        echo 'FAILED';
    }
    foreach ($points as $point):
        if ($i % 2 ==0) {
            $color=' style="background-color:#ddd"';
        } else {
            $color='';
        }
        $i++;
        ?><tr <?php echo $color; ?>>
        <td><?php echo $point['Timestamp']; ?>&nbsp;</td>
        <td><?php echo $point['DeliveryAttempts']; ?></td>
        <td><?php echo $point['Bounces']; ?></td>
        <td><?php echo $point['Complaints']; ?></td>
        <td><?php echo $point['Rejects']; ?></td>
        <td><?php echo $point['DeliveryAttempts']-$point['Bounces']-$point['Complaints']-$point['Rejects']; ?></td>
        <td><?php echo $point['Bounces']+$point['Complaints']+$point['Rejects']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
</div>