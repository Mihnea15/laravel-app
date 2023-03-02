<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Type\Time;
use function Symfony\Component\Translation\t;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $events = [];

        $appointments = Appointment::with(['client'])->get();

        foreach ($appointments as $appointment) {
            $events[] = [
                'title' => $appointment->client->name . ' ('.$appointment->employee->name.')',
                'start' => $appointment->start_time,
                'end' => $appointment->finish_time,
            ];
        }

        return view('home', compact('events'));
    }

    public function saveApp(Request $date)
    {
        if (strtotime($date->date) < strtotime(date('Y-m-d'))) {
            return \Illuminate\Support\Facades\Response::json('Appointments can not be made on the past', 500);
        }
        $dayofweek = date('w', strtotime($date->date));
        if ($dayofweek === '6' || $dayofweek === '0') {
            return \Illuminate\Support\Facades\Response::json('The appointment cannot be made on weekends', 500);
        }
        $appointments = Appointment::all();
        if (strtotime('09:00') <= strtotime($date->start) && strtotime($date->stop) <= strtotime('13:00')
            || strtotime('15:30') <= strtotime($date->start) && strtotime($date->stop) <= strtotime('21:00')) {
            if (round(abs(strtotime($date->start) - strtotime($date->stop)), 2) > 3600) {
                return \Illuminate\Support\Facades\Response::json('Appointment could not be greater than 1 hour', 500);
            }
            if ($appointments->isEmpty()) {
                $model = new Appointment();
                $model->start = $date->date . ' ' . $date->start;
                $model->stop = $date->date . ' ' . $date->stop;
                $model->client_id = 1;
                $model->save();
                return [$model->start, $model->stop];
            } else {
                $appointment = DB::table('appointments')
                    ->where('start', 'LIKE', '%' . $date->date . ' ' . explode(':', $date->start)[0] . '%')
                    ->where('stop', 'LIKE', '%' . $date->date . ' ' . explode(':', $date->stop)[0] . '%')->get();
                if (!$appointment->isEmpty()) {
                    return \Illuminate\Support\Facades\Response::json('An appointment for this interval already exist', 500);
                }
                foreach ($appointments as $appointment) {
                    $appointmentDay = explode(' ', $appointment->start)[0];
                    if ($date->date == $appointmentDay) {
                        $stopHour = explode(' ', $appointment->stop)[1];
                        if (round(abs(strtotime($date->start) - strtotime($stopHour)), 2) < 1200) {
                            return \Illuminate\Support\Facades\Response::json('Appointments must have half an hour between them', 500);
                        }
                    }
                }
                $model = new Appointment();
                $model->start = $date->date . ' ' . $date->start;
                $model->stop = $date->date . ' ' . $date->stop;
                $model->client_id = 1;
                $model->save();
                return [$model->start, $model->stop];
            }
        }
        return \Illuminate\Support\Facades\Response::json('Appointments must be between 09:00-13:00 or 15:30-21:00', 500);
    }

    public function checkExistingApp()
    {
        $appointments = Appointment::all();
        if (!$appointments->isEmpty()) {
            return $appointments;
        }
        return 'No appointments found';
    }
}
