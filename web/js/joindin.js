$(function(){

    function modifyAttendingCount(eventName, byAmount)
    {
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

        eventAttendingCountSpan.html(eventAttendingCount + byAmount);
    }

    $(this).find(".attending").on('click', function (evt) {

        evt.preventDefault();

        var dataAttr = $(this).data();
        var getUrl = baseUrl + '/event/xhr-attend/' + dataAttr.friendlyName;
        var deleteUrl = baseUrl + '/event/xhr-unattend/' + dataAttr.friendlyName;

        if (!$(this).hasClass('label-success')) {
            $.ajax({
                "type": "GET",
                "url": getUrl,
                "success": function (result) {
                    if (result.success) {

                        var eventName = $(this).data().friendlyName;

                        modifyAttendingCount(eventName, 1);

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
                    }
                }.bind(this),
                "dataType": "json"
            });
        } else {
            $.ajax({
                "type": "GET",
                "url": deleteUrl,
                "success": function (result) {
                    if (result.success) {

                        var eventName = $(this).data().friendlyName;

                        modifyAttendingCount(eventName, -1);

                        var attendanceString  = 'attending ';
                        if ($(this).data('is-past')) {
                            attendanceString = 'attended ';
                        }
                        var eventAttendingString = $('.' + eventName + '-attending-string');

                        if (eventAttendingString.length  == 0) {
                            $(this).before("<span class='" + eventName + "-attending-string'></span>");
                            eventAttendingString = $('.' + eventName + '-attending-string');
                        }

                        eventAttendingString.html(attendanceString);
                        eventAttendingString.removeClass('hide');

                        if ($(this).data('is-past')) {
                            $(this).html('I went to this event');
                        } else {
                            $(this).html('I will be attending');
                        }
                        $(this).addClass('btn btn-xs btn-primary');
                        $(this).removeClass('label label-success');
                    }
                }.bind(this),
                "dataType": "json"
            });
        }
    });

    // Check URL hash on page load and every time it changes
    checkUrlHash();
    $(window).on('hashchange', checkUrlHash);

    /**
     * Get hash part from URL and if it starts with "comment", highlight the comment with that id
     */
    function checkUrlHash() {
        var hash = window.location.hash.substr(1);

        if (hash.length && hash.indexOf('comment') == 0) {
            highlightComment(hash);
        }
    }

    /**
     * Find panel element inside the comment container and change the bootstrap panel style
     * from "default" (gray) to "info" (light blue) + add "flash" class to highlight the given comment.
     *
     * @param elementId
     */
    function highlightComment(elementId) {
        $('#' + elementId)
            .find('.panel')
            .removeClass('panel-default')
            .addClass('panel-info')
            .addClass('flash');
    }
});
