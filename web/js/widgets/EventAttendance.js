$.widget("widget.EventAttendance", {

    options: {
        'xhrUrlPart': '/event/xhr-attend/'
    },

    _create: function () {
        $(this.element).find('.button').on('click', function (evt) {
            evt.preventDefault();
            this._confirm();
        }.bind(this));
    },

    _confirm: function () {

        var dataAttr = $(this.element).find('.button').data();
        var getUrl = this.options.localUrl + this.options.xhrUrlPart + dataAttr.friendlyName;

        $.ajax({
            "type": "GET",
            "url": getUrl,
            "success": function (result) {
                if (result.success) {
                    var button = $(this.element).find('.button');
                    var eventName = button.data().friendlyName;
                    var eventAttendingCountSpan = $(this.element).find('.event-attending-count');
                    var eventAttendingCount = parseInt(eventAttendingCountSpan.text());
                    eventAttendingCountSpan.html(++eventAttendingCount);
                    button.remove();
                    $(this.element).find('.event-user-attending').html('(including you)');
                }
            }.bind(this),
            "dataType": "json"
        });
    }
});
