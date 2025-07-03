Pman.Core = Pman.Core || {};
/**
 * Usage:
 *   new Roo.Core.NotifySend([....])
 *
 * @param {array} notifications array of core_notify object
 */
Pman.Core.NotifySend = function(notifications)
{
    this.notifications = notifications;
    this.sentMsgs = [];
    this.errorMsgs = [];
    this.total = notifications.length;
    Roo.MessageBox.progress("Email Sending", "Starting");
    this.sendEmail();
}
    
Roo.apply(Pman.Core.NotifySend.prototype, {    
    
    notifications : false,
    sentMsgs : false,
    errorMsgs : false,
    total : false,
    i : 0,
   
    sendEmail : function()
    {
        
        new Pman.Request({
            url: baseURL + '/Core/NotifySend/' + this.notifications[this.i]['id'],
            params: {
                force: 1
            },
            method: 'POST',
            success: function(res)
            {
                this.sentMsgs.push(this.notifications[this.i]['to_email']);
                this.postSend();
            },
            failure: function (res)
            {
                this.errorMsgs.push(this.notifications[this.i]['to_email'] + ': ' + res.errorMsg);
                this.postSend();
            },
            scope : this
        });
    },
    postSend : function() {
        this.i++;
        Roo.MessageBox.updateProgress(this.i / this.total,
            this.i + " / " + this.total + " emails sent");
        if(this.i >= this.total) {
            Roo.MessageBox.hide();

            var msg = '';

            if(this.sentMsgs.length) {
                msg += 'This has been sent to:<br>';
                msg += this.sentMsgs.join('<br>');
            }

            if(this.errorMsgs.length) {
                if(msg.length) {
                    msg += '<br><br>';
                }
                msg += 'Failed to send to:<br>';
                msg += this.errorMsgs.join('<br>');

                Roo.log(regex);
                Roo.log(this.errorMsgs[0]);
                Roo.log(regex.test(this.errorMsgs[0]));
                var regex = '/^FAILED - 535:5.7.3 Authentication unsuccessful/';
                if(regex.test(this.errorMsgs[0])) {
                    msg = 'LOGIN IN AGAIN';
                }


            }

            Roo.MessageBox.alert('Result', msg);
            return;
        }
        this.sendEmail();
    }
});