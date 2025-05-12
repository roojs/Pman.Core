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
    this.errorMsgs = [];
    this.total = notifications.length;
    Roo.MessageBox.progress("Email Sending", "Starting");
    this.sendEmail();
}
    
Roo.apply(Pman.Core.NotifySend.prototype, {    
    
    notifications : false,
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
            
            // show errors if any
            if(this.errorMsgs.length) {
                Roo.MessageBox.alert('Error', this.errorMsgs.join('<br>'));
                return;
            }
            Roo.MessageBox.alert('Sent', 'Your message was successfully sent');
            return;
        }
        this.sendEmail();
    }
});