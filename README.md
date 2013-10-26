# SendPress Extended

A wordpress plugin to extend functionality of SendPress (https://github.com/brewlabs/sendpress) to support sending via SES.

The name is very generic because this was just used for a client project, but since I've ended up using it for multiple projects and moved it to a common place, I'm sharing it as is. Hopefully it will be useful to others.

# Use

 1. Install SendPress (http://sendpress.com/) (tested under 0.9.5.4)
 2. Install this plugin
 3. Fill in the necessary parts in the AWS SES Email settings under "Settings"
 4. Choose "Activate SES" in the SendPress Settings "Sending Account" page

# Known Issues

 - Sending is somewhat slow when viewing the queue even when the send limits are pretty high. This is a limitation of the way SendPress sends AJAX requests to send one email at a time. It may be faster during cron sending, but I haven't done extensive testing

# Possible Issues

 - There could be failures due to AWS rate limits. I added a pretty simple check to limit sending for a single running application, but it is not concurrent and therefore multiple application instances can send emails that exceed the AWS rates and fail. This should be handled but I have not tested it. Use at your own risk
 - This is running on a few production servers but hasn't been tested extensively. There could be any number of bugs
 - This could break with newer versions of SendPress

# License

Parts of the code are based on this plugin (http://wp-ses.com/). The license of which is unknown, so at the very least I'm giving the developer credit.

The SES library is licensed under a modified BSD license and so this plugin too is licensed under a modified BSD license in the interest of simplicity.

Feel free to fork this and I appreciate any helpful pull requests!