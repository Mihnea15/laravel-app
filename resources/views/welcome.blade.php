<!DOCTYPE html>
<html lang='en'>
    <head>
        <meta charset='utf-8' />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js'></script>
        <script>

            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                });
                calendar.render();
            });
            $(document).on('click', '.fc-day', function (el) {
                addAppointment(el.currentTarget)
            })
        </script>
    </head>
    <body>
        <div id='calendar'></div>
        <div>
            <button class="btn btn-success">Submit appointment</button>
        </div>
    </body>
</html>

<script>
    let timeIntervals = [];
    function addAppointment(el) {
        let date = $(el).attr('data-date')
        if (!$('.fc-daygrid-day-events').find('.start-time-picker_' + date).length) {
            $(el).find('.fc-daygrid-day-events').prepend(`
                <div class="app_"` + date + `>
                    <label>Appointment hours</label>
                    <br>
                    <input type="time" class="start-time-picker_` + date + `"> -
                    <input type="time" class="stop-time-picker_` + date + `">
                    <button class="new_app" onclick="saveApp()">Add another appointment</button>
                </div>
            `);
        }

        $('.start-time-picker_' + date).on('change', function () {
            timeIntervals['date'] = $(el).attr('data-date');
            timeIntervals['start'] = $(this).val();
        });
        $('.stop-time-picker_' + date).on('change', function () {
            timeIntervals['stop'] = $(this).val();
        });
    }

    function saveApp()
    {
        let date = timeIntervals['date'];
        let start = timeIntervals['start'];
        let stop = timeIntervals['stop']
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        $.ajax({
            type: 'post',
            enctype: 'multipart/form-data',
            url: "/saveApp",
            // dataType: "json",
            data: {
                date: date,
                start: start,
                stop: stop
            },
            success: function(result){
                console.log(result);
            },
            error: function (result) {
                alert(result.responseJSON)
            }
        });
    }
</script>

