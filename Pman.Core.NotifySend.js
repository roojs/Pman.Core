Pman.Core = Pman.Core || {};

Pman.Core.NotifySend = {
    /**
     * @param {array} notifications array of core_notify object
     */
    sendNotifications: function(notifications) {
        var errorMsgs = [];
                        
        var i = 0;  
        var total = notificaitons.length;
        
        var postSend = function() {
            i++;
            Roo.MessageBox.updateProgress(i / total,
                i + " / " + total + " emails sent");
            if(i >= total) {
                Roo.MessageBox.hide();
                
                // show errors if any
                if(errorMsgs.length) {
                    Roo.MessageBox.alert('Error', errorMsgs.join('<br>'));
                    return;
                }
                Roo.MessageBox.alert('Sent', 'Your message was successfully sent');
                return;
            }
            sendEmail();
        }
        
        var sendEmail = function() {
            var notificationId = notifications[i]['id'];
            new Pman.Request({
                url: baseURL + '/Core/NotifySend/' + notificationId,
                params: {
                    force: 1
                },
                method: 'POST',
                success: function(res)
                {
                    postSend();
                },
                failure: function (res)
                {
                    errorMsgs.push(notifications[i]['to_email'] + ': ' + res.errorMsg);
                    postSend();
                }
            });
        };
        
        Roo.MessageBox.progress("Email Sending", "Starting");
        
        sendEmail();
    }
};