<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
            } else {
                foreach ($appointments as $appointment) {
                    $day = explode(' ', $appointment->start)[0];
                    $startHour = explode(' ', $appointment->start)[1];
                    $stopHour = explode(' ', $appointment->stop)[1];
                    if ($day == $date->date) {
                        if ($startHour == $date->start . ':00' || $stopHour == $date->stop . ':00') {
                            return \Illuminate\Support\Facades\Response::json('An appointment for this interval already exist', 500);
                        }
                        var_dump(round(abs(strtotime($stopHour) - strtotime($date->start)), 2));
                        if (round(abs(strtotime($stopHour) - strtotime($date->start)), 2) < 1200) {
                            return \Illuminate\Support\Facades\Response::json('Appointments must have half an hour between them', 500);
                        }
                    }
                }
            }
            return true;
        }
        return \Illuminate\Support\Facades\Response::json('Invalid interval', 500);
    }
}
