/* Global page content loading for JS, this is used on-demand */


// Loads the calendar, tasks, and other information -> references external HTML directory for template content
function loadCalendarPane(into) {
    if(into === undefined) // if we didn't specify the place to load into when calling the function
        into = "cal_email_tasks"; // load it into the cal_email_tasks block

    $("#" + into).load("/html/right_panel.php", function() {
        $(function() {
            //$("#calendar-tab").on("shown.bs.tab", function() { // Commented out while calendar is the first tab active
            $("#calendar_display").fullCalendar({
                aspectRatio: 2.4,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'listDay,listWeek,month'
                },

                // customize the button names,
                // otherwise they'd all just say "list"
                views: {
                    listDay: { buttonText: 'list day' },
                    listWeek: { buttonText: 'list week' }
                },

                defaultView: 'listWeek',
                defaultDate: '2017-04-12',
                navLinks: true, // can click day/week names to navigate views
                editable: true,
                eventLimit: true, // allow "more" link when too many events
                events: [
                    {
                        title: 'All Day Event',
                        start: '2017-04-01'
                    },
                    {
                        title: 'Long Event',
                        start: '2017-04-07',
                        end: '2017-04-10'
                    },
                    {
                        id: 999,
                        title: 'Repeating Event',
                        start: '2017-04-09T16:00:00'
                    },
                    {
                        id: 999,
                        title: 'Repeating Event',
                        start: '2017-04-16T16:00:00'
                    },
                    {
                        title: 'Conference',
                        start: '2017-04-11',
                        end: '2017-04-13'
                    },
                    {
                        title: 'Meeting',
                        start: '2017-04-12T10:30:00',
                        end: '2017-04-12T12:30:00'
                    },
                    {
                        title: 'Lunch',
                        start: '2017-04-12T12:00:00'
                    },
                    {
                        title: 'Meeting',
                        start: '2017-04-12T14:30:00'
                    },
                    {
                        title: 'Happy Hour',
                        start: '2017-04-12T17:30:00'
                    },
                    {
                        title: 'Dinner',
                        start: '2017-04-12T20:00:00'
                    },
                    {
                        title: 'Birthday Party',
                        start: '2017-04-13T07:00:00'
                    },
                    {
                        title: 'Click for Google',
                        url: 'http://google.com/',
                        start: '2017-04-28'
                    }
                ]
            });
            //}); // Commented out while calendar is the first tab active
        });
    });
}