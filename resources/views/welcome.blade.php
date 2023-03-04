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
    <h1 class="text-center">Appointment calendar</h1>
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
                    <button class="btn btn-sm btn-danger remove-app float-end">&#10006</button>
                    <span>Appointment intervals</span>
                    <br>
                    <small>(09:00-13:00 / 15:30-21:00)</small>
                    <br>
                    <input type="time" class="timepicker_start form-control mb-2 start-time-picker_` + date + `">
                    <input type="time" class="timepicker_stop form-control stop-time-picker_` + date + `">
                    <button class="new_app btn btn-secondary mt-2 mb-2 w-100" onclick="saveApp()">Add another appointment</button>
                </div>
            `);
        }

        $('.remove-app').on('click', function () {
            $('.app_' + date).remove();
        });

        $('.start-time-picker_' + date).on('change', function () {
            timeIntervals['date'] = $(el).attr('data-date');
            timeIntervals['start'] = $(this).val();
        });
        $('.stop-time-picker_' + date).on('change', function () {
            timeIntervals['stop'] = $(this).val();
        });
    }

    function removeApp(el)
    {
        let dateToRm = $(el).attr('data-date');
        let id = $(el).attr('data-id');
        setTimeout(function () {
            $('.app_' + dateToRm).remove();
        }, 1);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'get',
            enctype: 'multipart/form-data',
            url: "/removeApp",
            // dataType: "json",
            data: {id: id},
            success: function(result) {
                $('div[data-id="' + id + '"]').remove();
            },
            error: function (result) {
                alert(result.responseJSON);
            }
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
                        <div data-id="` + result[i]['id'] + `">
                            <button class="float-end btn btn-sm pt-0 ml-2" data-id="` + result[i]['id'] + `" data-date="` + date + `" onclick="removeApp(this)">&#10006</button>
                            <div style="background-color: #7d7dd6; text-align: center; color: white; border-radius: 20px;">
                                <p>` + startHour + `-` + stopHour +`</p>
                            </div>
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
                $('td[data-date="' + date + '"]').find('.fc-daygrid-day-events').append(`
                    <div data-id="` + result[0] + `">
                        <button class="float-end btn btn-sm pt-0 ml-2" data-id="` + result[0] + `" data-date="` + date + `" onclick="removeApp(this)">&#10006</button>
                        <div style="background-color: #7d7dd6; text-align: center; color: white; border-radius: 20px;">
                                <p>` + result[1].split(' ')[1] + ':00' + `-` + result[2].split(' ')[1] + ':00' +`</p>
                        </div>
                    </div>
                `);
                $('.app_' + date).remove();
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

