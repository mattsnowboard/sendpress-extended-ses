<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2><?php _e('AWS SES Email Options', 'aws_ses_email') ?></h2>
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <?php wp_nonce_field('aws_ses_email'); ?>
        <h3><?php _e('Plugin State','aws_ses_email') ?>&nbsp;<input type="submit" name="refresh" value="<?php _e('Refresh','aws_ses_email') ?>" /></h3>
    </form>  
    <ul>
        <?php
        if ($aws_ses_email_options['from_email'] != '') {
            echo('<li style="color:#0f0;">');
            _e("Sender Email is set ", 'aws_ses_email');
        } else {
            echo('<li style="color:#f00;">');
            _e("Sender Email is not set ", 'aws_ses_email');
        }
        ?></li>
        <?php
        if ($aws_ses_email_options['credentials_ok'] == 1) {
            echo('<li style="color:#0f0;">');
            _e("Amazon API Keys are valid", 'aws_ses_email');
        } else {
            echo('<li style="color:#f00;">');
            _e("Amazon API Keys are not valid, or you did not finalize your Amazon SES registration.",
               'aws_ses_email');
        }
        ?></li>
        <?php
        if ($aws_ses_email_options['sender_ok'] == 1) {
            echo('<li style="color:#0f0;">');
            _e("Sender Email has been confirmed.", 'aws_ses_email');
        } else {
            echo('<li style="color:#f00;">');
            _e("Sender Email has not been confirmed yet.", 'aws_ses_email');
        }
        ?></li>  	

        <?php
        if ($aws_ses_email_options['active'] == 1):
            echo('<li style="color:#0f0;">');
            _e("Plugin is active.", 'aws_ses_email');
            echo("<br /><b>");
            _e('You can check your sending limits and stats under Dashboard -> SES Stats',
               'aws_ses_email');
            echo("</b>");
            ?><form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <?php wp_nonce_field('aws_ses_email'); ?>
                <p class="submit">
                    <input type="submit" name="deactivate" value="<?php _e('De-activate Plugin', 'aws_ses_email') ?>" />
                </p><?php _e('If you want to test further, de-activate the plugin here. Outgoing mails will be delivered by the default wordpress method, but you\'ll still be able to test custom SES email delivery.', 'aws_ses_email') ?>
            </form>
        <?php
        else:
            echo('<li style="color:#f00;">');
            _e("Plugin is not active.", 'aws_ses_email');
            ?>
            <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                <?php wp_nonce_field('aws_ses_email'); ?>
                <p class="submit">
                    <input type="submit" name="activate" value="<?php _e('Activate plugin', 'aws_ses_email') ?>" />
                </p><?php _e('Warning: Activate only if your account is in production mode.<br />Once activated, all outgoing emails will go through Amazon SES and will NOT be sent to any email while in sandbox.', 'aws_ses_email') ?>
            </form>  		
        <?php endif; ?>
    </li>


    </ul>
    <h3><?php _e('Sender Email', 'aws_ses_email') ?></h3>
    <?php _e('These settings do replace default sender email used by your blog.', 'aws_ses_email') ?>
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <?php wp_nonce_field('aws_ses_email'); ?>
        <table class="form-table">
            <tr><th scope="row"><?php _e('Sender Email', 'aws_ses_email') ?></th>
                <td><input type="text" name="from_email" value="<?php echo $aws_ses_email_options['from_email']; ?>" />&nbsp;<?php _e('(Has to be a valid Email)', 'aws_ses_email') ?></td></tr>
            <tr><th scope="row"><?php _e('Name', 'aws_ses_email') ?></th>
                <td><input type="text" name="from_name" value="<?php echo $aws_ses_email_options['from_name']; ?>" /></td></tr>
            <tr><th scope="row"><?php _e('Return Path', 'aws_ses_email') ?></th>
                <td><input type="text" name="return_path" value="<?php echo $aws_ses_email_options['return_path']; ?>" />&nbsp;<?php _e('You can specify a return Email (not required).<br />Delivery Status notification messages will be sent to this address.', 'aws_ses_email') ?></td></tr>
        </table>

        <h3><?php _e("Amazon API Keys", 'aws_ses_email') ?></h3>
        <div style="border:1px solid#ccc; padding:10px; float:right; ">
            If you already use an Amazon Web service like S3,<br />
            you can use the very same keys here.
        </div>
    <?php _e('Please insert here your API keys given by the Amazon Web Services.', 'aws_ses_email') ?>
        <table class="form-table" style="width:450px; float:left;" width="450">
            <tr><th scope="row"><?php _e('access_key', 'aws_ses_email') ?></th>
                <td><input type="text" name="access_key" value="<?php echo $aws_ses_email_options['access_key']; ?>" /></td></tr>
            <tr><th scope="row"><?php _e('secret_key', 'aws_ses_email') ?></th>
                <td><input type="text" name="secret_key" value="<?php echo $aws_ses_email_options['secret_key']; ?>" /></td></tr>
        </table>

        <input type="hidden" name="action" value="update" />
        <!-- input type="hidden" name="page_options" value="aws_ses_email_options" / -->
        <p class="submit" style="clear:both">
            <input type="submit" name="save" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
    <br />&nbsp;

    <h3><?php _e("Confirmed senders", 'aws_ses_email') ?></h3>
            <?php _e('Only confirmed senders are able to send an email via SES', 'aws_ses_email') ?><br />
            <?php _e('The following senders are known:', 'aws_ses_email') ?>
    <br />
    <div style="width:70%">
        <table class="form-table">
            <tr style="background-color:#ccc; font-weight:bold;">
                <td><?php _e('Email', 'aws_ses_email') ?></td>
                <td><?php _e('Request Id', 'aws_ses_email') ?></td>
                <td><?php _e('Confirmed', 'aws_ses_email') ?></td>
            </tr>
        <?php
        $i = 0;
        foreach ($senders as $email => $props) {
            if ($i % 2 == 0) {
                $color = ' style="background-color:#ddd"';
            } else {
                $color = '';
            }
            echo("<tr$color>");
            echo("<td>$email</td>");
            echo("<td>");
            echo("</td>");
            echo("<td>" . (($props[1]) ? __('Yes', 'aws_ses_email') : __('No', 'aws_ses_email')) . "</td>");
            echo("</tr>");
            $i++;
        }
        ?>
        </table>
        <table class="form-table">
            <tr style="background-color:#ccc; font-weight:bold;">
                <td><?php _e('Domain', 'aws_ses_email') ?></td>
                <td><?php _e('Request Id', 'aws_ses_email') ?></td>
                <td><?php _e('Confirmed', 'aws_ses_email') ?></td>
            </tr>
        <?php
        $i = 0;
        foreach ($sender_domains as $email => $props) {
            if ($i % 2 == 0) {
                $color = ' style="background-color:#ddd"';
            } else {
                $color = '';
            }
            echo("<tr$color>");
            echo("<td>$email</td>");
            echo("<td>");
            echo("</td>");
            echo("<td>" . (($props[1]) ? __('Yes', 'aws_ses_email') : __('No', 'aws_ses_email')) . "</td>");
            echo("</tr>");
            $i++;
        }
        ?>
        </table>
    </div>
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php wp_nonce_field('aws_ses_email'); ?>
        <br />
<?php _e('Add the following email: ', 'aws_ses_email') ?><?php echo $aws_ses_email_options['from_email']; ?><?php _e(' to senders.', 'aws_ses_email') ?>

        <p class="submit">
            <input type="submit" name="addemail" value="<?php _e('Add this Email', 'aws_ses_email') ?>" />
        </p>
    </form>
    <br />&nbsp;

    <h3><?php _e('Test Email', 'aws_ses_email') ?></h3>
        <?php _e('Click on this button to send a test email (via amazon SES) to the sender email.', 'aws_ses_email') ?>
    <br />
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php wp_nonce_field('aws_ses_email'); ?>
        <p class="submit">
            <input type="submit" name="testemail" value="<?php _e("Send Test Email", 'aws_ses_email') ?>" />
        </p>
    </form>
    <br />&nbsp;
    <h3><?php _e('Production mode test', 'aws_ses_email') ?></h3>
<?php _e('Once Amazon did activate your account into production mode, you can begin to send mail to any address<br />Use the form below to test this before fully activating the plugin on your blog.', 'aws_ses_email') ?>
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php wp_nonce_field('aws_ses_email'); ?>
        <table class="form-table" >
            <tr><th scope="row"><?php _e('Send email to ', 'aws_ses_email') ?></th>
                <td><input type="text" name="prod_email_to" value="" /></td></tr>
            <tr><th scope="row"><?php _e('Subject', 'aws_ses_email') ?></th>
                <td><input type="text" name="prod_email_subject" value="" /></td></tr>
            <tr><th scope="row"><?php _e('Mail content', 'aws_ses_email') ?></th>
                <td><textarea cols="80" rows="5" name="prod_email_content"></textarea></td></tr>
        </table>
        <p class="submit">
            <input type="submit" name="prodemail" value="<?php _e("Send Full Test Email", 'aws_ses_email') ?>" />
        </p>
    </form>
    <br />&nbsp;
</div>