<style>
    input.timepicker_start::-webkit-calendar-picker-indicator{
        display:block;
        top:0;
        left:0;
        background: #0000;
        position:absolute;
        transform: scale(12)
    }
    input.timepicker_stop::-webkit-calendar-picker-indicator{
        display:block;
        top:0;
        left:0;
        background: #0000;
        position:absolute;
        transform: scale(12)
    }
</style>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <meta charset='utf-8' />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js'></script>
        <script src="{{ asset('js/app.js') }}" defer></script>
        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <script>

            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                });
                calendar.render();
            });
            $(document).on('click', '.fc-day', function (el) {
                addAppointment(el.currentTarget);
            })
        </script>
    </head>
    <body>
        <div id='calendar'></div>
        <div>
        </div>
    </body>
</html>

<script>
    let timeIntervals = [];
    let date = null;
    function addAppointment(el) {
        date = $(el).attr('data-date');
        if (!$('.fc-daygrid-day-events').find('.start-time-picker_' + date).length) {
            $(el).find('.fc-daygrid-day-events').prepend(`
                <div class="app_` + date + ` col-9 container">
                    <label>Appointment hours</label>
                    <br>
                    <input type="time" class="timepicker_start form-control start-time-picker_` + date + `">
                    <input type="time" class="timepicker_stop form-control stop-time-picker_` + date + `">
                    <button class="new_app btn btn-secondary mt-2 mb-2 w-100" onclick="saveApp()">Add another appointment</button>
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

    function checkExistingApp()
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'post',
            enctype: 'multipart/form-data',
            url: "/checkExistingApp",
            // dataType: "json",
            data: {
            },
            success: function(result){
                for (let i = 0; i < result.length; i++) {
                    let date = result[i]['start'].split(' ')[0];
                    let startHour = result[i]['start'].split(' ')[1];
                    let stopHour = result[i]['stop'].split(' ')[1];
                    $('td[data-date="' + date + '"]').find('.fc-daygrid-day-events').append(`
                        <div style="background-color: #7d7dd6; text-align: center; color: white; border-radius: 20px;">
                            <p>` + startHour + `-` + stopHour +`</p>
                        </div>
                    `);
                }
            },
            error: function (result) {
                alert(result.responseJSON);
            }
        });
    }

    function saveApp()
    {
        let date = timeIntervals['date'];
        let start = timeIntervals['start'];
        let stop = timeIntervals['stop'];
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
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
            success: function(result) {
                $('.app_' + date).append(`
                    <div style="background-color: #7d7dd6; text-align: center; color: white; border-radius: 20px;">
                            <p>` + result[0].split(' ')[1] + `-` + result[1].split(' ')[1] +`</p>
                    </div>
                `)
            },
            error: function (result) {
                alert(result.responseJSON);
            }
        });
    }

    $(document).ready(function () {
        checkExistingApp();
        $('.fc-today-button').on('click', function () {
            checkExistingApp();
        });
        $('.fc-prev-button ').on('click', function () {
            checkExistingApp();
        });
    });
</script>

