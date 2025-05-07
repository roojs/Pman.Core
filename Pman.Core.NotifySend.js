Pman.Core.NotifySend = {
    emails: [],

    sendEmails: function(emails, callback) {
        this.emails = emails
    },

    sendEmail: function() {
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
};