$(function(){

    $(this).find(".attending").on('click', function (evt) {

        evt.preventDefault();

        var dataAttr = $(this).data();
        var getUrl = baseUrl + '/event/xhr-attend/' + dataAttr.friendlyName;

        if (!$(this).hasClass('label-success')) {
            $.ajax({
                "type": "GET",
                "url": getUrl,
                "success": function (result) {
                    if (result.success) {

                        var eventName = $(this).data().friendlyName;

                        var eventAttendingCountSpan = $('.' + eventName + '-attending-count');

                        if (eventAttendingCountSpan.length  == 0) {
                            $(this).before("<span class='" + eventName + "-attending-count'></span>");
                            eventAttendingCountSpan = $('.' + eventName + '-attending-count');
                        }

                        var existingEventAttendingCount = parseInt(eventAttendingCountSpan.html());

                        var eventAttendingCount = 0;
                        if (existingEventAttendingCount > 0) {
                            eventAttendingCount = existingEventAttendingCount;
                        }

                        eventAttendingCountSpan.html(++eventAttendingCount);

                        var attendanceString  = 'attending ';
                        if($.trim($(this).html()) == 'I went to this event') {
                            attendanceString = 'attended ';
                        }

                        var eventAttendingString = $('.' + eventName + '-attending-string');

                        if (eventAttendingString.length  == 0) {
                            $(this).before("<span class='" + eventName + "-attending-string'></span>");
                            eventAttendingString = $('.' + eventName + '-attending-string');
                        }

                        eventAttendingString.html(attendanceString);
                        eventAttendingString.removeClass('hide');

                        $(this).html('including you');
                        $(this).removeClass('btn btn-xs btn-primary');
                        $(this).addClass('label label-success');
                        $(this).css('cursor', 'default');
                    }
                }.bind(this),
                "dataType": "json"
            });
        }
    });
});
