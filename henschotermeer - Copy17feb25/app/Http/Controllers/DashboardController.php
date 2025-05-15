<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Dotenv;
use InvalidArgumentException;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Symfony\Component\Process\Process as Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\DevicePort;
use App\DeviceTicketReaders;
use App\LocationDevices;
use App\Http\Controllers\Settings\LocationSettings;
use App\Http\Controllers\Settings\Settings;
use GuzzleHttp\Client;

class DashboardController extends Controller
{

    public $controller = 'App\Http\Controllers\DashboardController';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public $url = "";

    public function __construct()
    {
        $this->middleware('auth');
        $this->url = env('API_BASE_URL');
    }

    /**
     * Get All Dashboard Data
     * @param Request $request
     * @return type
     */
    public function dashboard(Request $request)
    {
        $devices = $this->get_devices();
        $widget_1 = $this->get_widget1_details();
        $widget_2 = $this->get_widget2_details();
        $widget_3 = $this->get_widget3_details();
        $last_5_transactions = $this->get_last_5_transactions(5);
        $at_location_vehicle = $this->at_location_vehicles(5);
        $is_system_live = true;
        return view('dashboard.dashboard', [
            'at_location' => $at_location_vehicle,
            'at_location_vehicle' => $at_location_vehicle,
            'last_5_transactions' => $last_5_transactions,
            'is_system_live' => $is_system_live,
            'devices' => $devices,
            'widget_1' => $widget_1,
            'widget_2' => $widget_2,
            'widget_3' => $widget_3,
        ]);
    }

    /**
     * Get All Person Dashboard Data
     * @param Request $request
     * @return type
     */
    public function p_dashboard(Request $request)
    {
        $devices = $this->get_devices(true);
        $widget_1 = $this->get_widget1_details(true);
        $widget_2 = $this->get_widget2_details(true);
        $widget_3 = $this->get_widget3_details();
        $last_5_transactions = $this->get_last_5_transactions(5, true);
        $at_location_person = $this->at_location_persons();
        return view('dashboard.dashboard_p', [
            'at_location_person' => $at_location_person,
            'last_5_transactions' => $last_5_transactions,
            'devices' => $devices,
            'widget_1' => $widget_1,
            'widget_2' => $widget_2,
            'widget_3' => $widget_3,
        ]);
    }

    /**
     * Get All Devices Statuses
     * @param Request $request
     * @return type
     */
    public function get_all_devices_status()
    {
        $devices_details = array();
        $devices_details['total'] = array(
            'sync' => 0,
            'total' => 0,
        );
        $devices_details['ticket_reader'] = array(
            'sync' => 0,
            'total' => 0,
        );
        $devices_details['plate_reader'] = array(
            'sync' => 0,
            'total' => 0,
        );
        $devices_details['payment_terminal'] = array(
            'sync' => 0,
            'total' => 0,
        );
        $devices_details['person_ticket_reader'] = array(
            'sync' => 0,
            'total' => 0,
        );
        $location_devices = \App\LocationDevices::get();
        if ($location_devices->count() > 0) {
            foreach ($location_devices as $location_device) {
                if ($location_device->available_device_id == 1) {
                    $devices_details['ticket_reader']['total'] = $devices_details['ticket_reader']['total'] + 1;
                    if ($location_device->is_synched) {
                        $devices_details['ticket_reader']['sync'] = $devices_details['ticket_reader']['sync'] + 1;
                    }
                    $devices_details['total']['total'] = $devices_details['total']['total'] + 1;
                    if ($location_device->is_synched) {
                        $devices_details['total']['sync'] = $devices_details['total']['sync'] + 1;
                    }
                } elseif ($location_device->available_device_id == 2) {
                    $devices_details['person_ticket_reader']['total'] = $devices_details['person_ticket_reader']['total'] + 1;
                    if ($location_device->is_synched) {
                        $devices_details['person_ticket_reader']['sync'] = $devices_details['person_ticket_reader']['sync'] + 1;
                    }
                    $devices_details['total']['total'] = $devices_details['total']['total'] + 1;
                    if ($location_device->is_synched) {
                        $devices_details['total']['sync'] = $devices_details['total']['sync'] + 1;
                    }
                } elseif ($location_device->available_device_id == 3) {
                    $devices_details['plate_reader']['total'] = $devices_details['plate_reader']['total'] + 1;
                    if ($location_device->is_synched) {
                        $devices_details['plate_reader']['sync'] = $devices_details['plate_reader']['sync'] + 1;
                    }
                    $devices_details['total']['total'] = $devices_details['total']['total'] + 1;
                    if ($location_device->is_synched) {
                        $devices_details['total']['sync'] = $devices_details['total']['sync'] + 1;
                    }
                } elseif ($location_device->available_device_id == 6) {
                    $devices_details['payment_terminal']['total'] = $devices_details['payment_terminal']['total'] + 1;
                    if ($location_device->is_synched) {
                        $devices_details['payment_terminal']['sync'] = $devices_details['payment_terminal']['sync'] + 1;
                    }
                    $devices_details['total']['total'] = $devices_details['total']['total'] + 1;
                    if ($location_device->is_synched) {
                        $devices_details['total']['sync'] = $devices_details['total']['sync'] + 1;
                    }
                } else {
                    continue;
                }
            }
        }
        return $devices_details;
    }

    /**
     * Get Current Week Start And End Time
     * @param Request $request
     * @return type
     */
    public function get_current_week_start_end()
    {
        $ts = strtotime(date('y-m-d'));
        $start = strtotime('last sunday midnight', $ts);
        $end = strtotime('sunday this week', $ts);
        return (object) array('start' => date('Y-m-d 23:59:59', $start), 'end' => date('Y-m-d 23:59:59', $end));
    }

    /**
     * Get Previous Week Start And End Time
     * @param Request $request
     * @return type
     */
    public function get_previous_week_start_end()
    {
        $previous_week = strtotime("-1 week");
        $start_week = strtotime("last monday", $previous_week);
        $end_week = strtotime("next sunday", $start_week);
        return (object) array('start' => date('Y-m-d 00:00:00', $start_week), 'end' => date('Y-m-d 23:59:59', $end_week));
    }

    /**
     * Get All Bookings Of Week
     * @param Request $request
     * @return type
     */
    public function get_bookings($start_date, $end_date)
    {
        $bookings = \App\Bookings::with(
            'booking_payments'
        )->where([
            ['is_cancelled', 0]
        ])
            ->whereBetween('checkin_time', [$start_date, $end_date])->get();
        if ($bookings->count() > 0) {
            return $bookings;
        }
        return false;
    }

    /**
     * Get Current Week Bookings
     * @param Request $request
     * @return type
     */
    public function get_this_week_bookings()
    {
        $dates = $this->get_current_week_start_end();
        $bookings = $this->get_bookings($dates->start, $dates->end);
        if ($bookings) {
            return $bookings;
        }
        return array();
    }

    /**
     * Get Previous Week Bookings
     * @param Request $request
     * @return type
     */
    public function get_previous_week_bookings()
    {
        $dates = $this->get_previous_week_start_end();
        $bookings = $this->get_bookings($dates->start, $dates->end);
        if ($bookings) {
            return $bookings;
        }
        return array();
    }

    /**
     * Get Current Week Revenue
     * @param Request $request
     * @return type
     */
    public function get_this_week_revenue()
    {
        $revenue = 0;
        $dates = $this->get_current_week_start_end();
        $bookings = $this->get_bookings($dates->start, $dates->end);
        if ($bookings) {
            foreach ($bookings as $booking) {
                if (isset($booking->booking_payments->amount)) {
                    $revenue += $booking->booking_payments->amount;
                }
            }
        }
        return $revenue;
    }

    /**
     * Get Revenue Chart Data
     * @param Request $request
     * @return type
     */
    public function revenue_chart()
    {
        $revenue_array = array();
        $days = array();
        $week = $this->get_current_week_start_end();
        $start = date('Y-m-d', strtotime($week->start));
        for ($i = 1; $i <= 7; $i++) {
            $date = date('Y-m-d', strtotime($start . ' +' . $i . ' days'));
            $days[] = (string) date('d/M', strtotime($date));
            $start_date = date('Y-m-d 00:00:00', strtotime($date));
            $end_date = date('Y-m-d 23:59:59', strtotime($date));
            $revenue = 0;
            $bookings = $this->get_bookings($start_date, $end_date);
            if ($bookings) {
                foreach ($bookings as $booking) {
                    if (isset($booking->booking_payments->amount)) {
                        $revenue += $booking->booking_payments->amount;
                    }
                }
            }
            $revenue_array[] = $revenue;
        }

        return json_encode(array('labels' => $days, 'revenue' => $revenue_array));
    }

    /**
     * Get Charts Details
     * @param Request $request
     * @return type
     */
    public function visits_chart()
    {
        $week_booked_spots_perc = 0;
        $previous_week_booked_spots_perc = 0;
        $weekly_bookings = $this->get_this_week_bookings();
        $previous_week_bookings = $this->get_previous_week_bookings();
        $location_options = \App\LocationOptions::first();
        if (!$location_options) {
        }
        $total_spots = $location_options->total_spots;
        if ($total_spots > 0) {
            $week_booked_spots_perc = round(count($weekly_bookings) / $total_spots * 100, 1);
            $previous_week_booked_spots_perc = round(count($previous_week_bookings) / $total_spots * 100, 1);
        } else {
            $week_booked_spots_perc = 0;
            $previous_week_booked_spots_perc = 0;
        }
        return json_encode(array($week_booked_spots_perc, $previous_week_booked_spots_perc));
    }

    /**
     * Get Today Booking Details
     * @param Request $request
     * @return type
     */
    public function get_today_booking_details($type = null)
    {
        $total = array();
        $arrived = array();
        $expecting = array();
        $at_location = array();
        $date = date('Y-m-d H:i:s');
        $booking_type_count_expecting = array(
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0,
            '6' => 0,
            '7' => 0
        );
        $booking_type_count_arrived = array(
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0,
            '6' => 0,
            '7' => 0
        );
        $start = date('Y-m-d 00:00:00', strtotime($date));
        $end = date('Y-m-d 23:59:59', strtotime($date));
        $bookings = \App\Bookings::with('booking_payments')
            ->get();
        if ($bookings) {
            foreach ($bookings as $booking) {
                if ($booking->type == 1) {
                    if ((date('Y-m-d', strtotime($booking->checkout_time)) < date('Y-m-d'))) {
                        continue;
                    }
                    $attendant_details = \App\Attendants::where([
                        ['booking_id', $booking->id]
                    ])->first();
                    if ($attendant_details) {
                        $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                        if ($transaction) {
                            if ($transaction->check_in != null && $transaction->check_out == null) {
                                $at_location[] = $booking->id;
                                $total[] = $booking->id;
                            } elseif ($transaction->check_in != null && $transaction->check_out != null) {
                                if (date('Y-m-d', strtotime($transaction->check_in)) == date('Y-m-d')) {
                                    $total[] = $booking->id;
                                    $booking_type_count_arrived[1] = $booking_type_count_arrived[1] + 1;
                                    $arrived[] = $booking->id;
                                }
                            }
                        }
                    } else {
                        if ($booking->check_out > $date) {
                            $total[] = $booking->id;
                            $booking_type_count_expecting[1] = $booking_type_count_expecting[1] + 1;
                            $expecting[] = $booking->id;
                        }
                    }
                } elseif ($booking->type == 2) {
                    $st_location_vehicle = false;
                    $attendant_details = \App\Attendants::where([
                        ['booking_id', $booking->id]
                    ])->first();
                    if ($attendant_details) {
                        $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                        if ($transaction) {
                            if ($transaction->check_in != null && $transaction->check_out == null) {
                                $at_location[] = $booking->id;
                                $st_location_vehicle = true;
                            } elseif ($transaction->check_in != null && $transaction->check_out != null) {
                                if (date('Y-m-d', strtotime($transaction->check_in)) == date('Y-m-d')) {
                                    $booking_type_count_arrived[2] = $booking_type_count_arrived[2] + 1;
                                    $arrived[] = $booking->id;
                                } else {
                                    $booking_type_count_expecting[2] = $booking_type_count_expecting[2] + 1;
                                    $expecting[] = $booking->id;
                                }
                            }
                        }
                    } else {
                        $booking_type_count_expecting[2] = $booking_type_count_expecting[2] + 1;
                        $expecting[] = $booking->id;
                    }
                    //                    if (!$st_location_vehicle) {
                    //                        $booking_type_count_expecting[2] = $booking_type_count_expecting[2] + 1;
                    //                        $expecting[] = $booking->id;
                    //                    }
                    $total[] = $booking->id;
                } elseif ($booking->type == 3) {
                    $st_location_vehicle = false;
                    $attendant_details = \App\Attendants::where([
                        ['booking_id', $booking->id]
                    ])->first();
                    if ($attendant_details) {
                        $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                        if ($transaction) {
                            if ($transaction->check_in != null && $transaction->check_out == null) {
                                $at_location[] = $booking->id;
                                $st_location_vehicle = true;
                            } elseif ($transaction->check_in != null && $transaction->check_out != null) {
                                if (date('Y-m-d', strtotime($transaction->check_in)) == date('Y-m-d')) {
                                    $booking_type_count_arrived[3] = $booking_type_count_arrived[3] + 1;
                                    $arrived[] = $booking->id;
                                } else {
                                    $booking_type_count_expecting[3] = $booking_type_count_expecting[3] + 1;
                                    $expecting[] = $booking->id;
                                }
                            }
                        }
                    } else {
                        $booking_type_count_expecting[3] = $booking_type_count_expecting[3] + 1;
                        $expecting[] = $booking->id;
                    }
                    //                    if (!$st_location_vehicle) {
                    //                        $booking_type_count_expecting[3] = $booking_type_count_expecting[3] + 1;
                    //                        $expecting[] = $booking->id;
                    //                    }
                    $total[] = $booking->id;
                } elseif ($booking->type == 4) {
                    if ((date('Y-m-d', strtotime($booking->checkout_time)) < date('Y-m-d'))) {
                        continue;
                    }
                    $attendant_details = \App\Attendants::where([
                        ['booking_id', $booking->id]
                    ])->first();
                    if ($attendant_details) {
                        $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                        if ($transaction) {
                            if ($transaction->check_in != null && $transaction->check_out == null) {
                                $at_location[] = $booking->id;
                                $total[] = $booking->id;
                            } elseif ($transaction->check_in != null && $transaction->check_out != null) {
                                $total[] = $booking->id;
                                if (date('Y-m-d', strtotime($transaction->check_in)) == date('Y-m-d')) {
                                    $booking_type_count_arrived[4] = $booking_type_count_arrived[4] + 1;
                                    $arrived[] = $booking->id;
                                }
                            }
                        }
                    } else {
                        if ($booking->check_out > $date) {
                            $total[] = $booking->id;
                            $booking_type_count_expecting[4] = $booking_type_count_expecting[4] + 1;
                            $expecting[] = $booking->id;
                        }
                    }
                } elseif ($booking->type == 5) {
                    if ((date('Y-m-d', strtotime($booking->created_at)) != date('Y-m-d'))) {
                        $attendant_details = \App\Attendants::where([
                            ['booking_id', $booking->id]
                        ])->first();
                        if ($attendant_details) {
                            $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                            if ($transaction) {
                                if ($transaction->check_in != null && $transaction->check_out == null) {
                                    $at_location[] = $booking->id;
                                    $total[] = $booking->id;
                                }
                            }
                        }
                        continue;
                    }
                    $attendant_details = \App\Attendants::where([
                        ['booking_id', $booking->id]
                    ])->first();
                    if ($attendant_details) {
                        $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                        if ($transaction) {
                            if ($transaction->check_in != null && $transaction->check_out == null) {
                                $at_location[] = $booking->id;
                                $total[] = $booking->id;
                            } elseif ($transaction->check_in != null && $transaction->check_out != null) {
                                if (date('Y-m-d', strtotime($transaction->check_in)) == date('Y-m-d')) {
                                    $total[] = $booking->id;
                                    $booking_type_count_arrived[5] = $booking_type_count_arrived[5] + 1;
                                    $arrived[] = $booking->id;
                                }
                            }
                        }
                    } else {
                        continue;
                    }
                } elseif ($booking->type == 6) {
                    if ((date('Y-m-d', strtotime($booking->checkout_time)) < date('Y-m-d'))) {
                        continue;
                    }
                    $st_location_vehicle = false;
                    $attendant_details = \App\Attendants::where([
                        ['booking_id', $booking->id]
                    ])->first();
                    if ($attendant_details) {
                        $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                        if ($transaction) {
                            if ($transaction->check_in != null && $transaction->check_out == null) {
                                $at_location[] = $booking->id;
                                $st_location_vehicle = true;
                            } elseif ($transaction->check_in != null && $transaction->check_out != null) {
                                if (date('Y-m-d', strtotime($transaction->check_in)) == date('Y-m-d')) {
                                    $booking_type_count_arrived[6] = $booking_type_count_arrived[6] + 1;
                                    $arrived[] = $booking->id;
                                }
                            }
                        }
                    } else {
                        $st_location_vehicle = true;
                        $booking_type_count_expecting[6] = $booking_type_count_expecting[6] + 1;
                        $expecting[] = $booking->id;
                    }
                    if (!$st_location_vehicle) {
                        $booking_type_count_expecting[6] = $booking_type_count_expecting[6] + 1;
                        $expecting[] = $booking->id;
                    }
                    $total[] = $booking->id;
                } elseif ($booking->type == 7) {
                    if ((date('Y-m-d', strtotime($booking->checkout_time)) < date('Y-m-d'))) {
                        continue;
                    }
                    $st_location_vehicle = false;
                    $attendant_details = \App\Attendants::where([
                        ['booking_id', $booking->id]
                    ])->first();
                    if ($attendant_details) {
                        $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                        if ($transaction) {
                            if ($transaction->check_in != null && $transaction->check_out == null) {
                                $at_location[] = $booking->id;
                                $st_location_vehicle = true;
                            } elseif ($transaction->check_in != null && $transaction->check_out != null) {
                                if (date('Y-m-d', strtotime($transaction->check_in)) == date('Y-m-d')) {
                                    $booking_type_count_arrived[6] = $booking_type_count_arrived[6] + 1;
                                    $arrived[] = $booking->id;
                                }
                            }
                        }
                    } else {
                        $st_location_vehicle = true;
                        $booking_type_count_expecting[6] = $booking_type_count_expecting[6] + 1;
                        $expecting[] = $booking->id;
                    }
                    if (!$st_location_vehicle) {
                        $booking_type_count_expecting[6] = $booking_type_count_expecting[6] + 1;
                        $expecting[] = $booking->id;
                    }
                    $total[] = $booking->id;
                } else {
                    continue;
                }
            }
        }
        $response = array();
        $response['total'] = $total;
        $response['expecting'] = $expecting;
        $response['arrived_booking_count'] = $booking_type_count_arrived;
        $response['expecting_booking_count'] = $booking_type_count_expecting;
        $response['arrived'] = $arrived;
        $response['at_location'] = $at_location;
        if ($type == 1) {
            $response['total'] = count($total);
            $response['expecting'] = count($expecting);
            $response['arrived'] = count($arrived);
            $response['at_location'] = count($at_location);
        } elseif ($type == 2) {
            if (count($total) == 0) {
                $response['total'] = 0;
                $response['expecting'] = 0;
                $response['arrived'] = 0;
                $response['at_location'] = 0;
            } else {
                $response['total'] = count($total);
                $response['expecting'] = round(count($expecting) / count($total) * 100);
                $response['arrived'] = round(count($arrived) / count($total) * 100);
                $response['at_location'] = round(count($at_location) / count($total) * 100);
            }
        }

        return (object) $response;
    }

    /**
     * Get Booking Calendar Data
     * @param Request $request
     * @return type
     */
    public function bookings_calender(Request $request)
    {
        $response = array();
        $start_date = date('Y-m-1');
        $start_month = date('Y-m-1');
        $end_date = date('Y-m-t');
        $end_month = date('Y-m-t');
        $record_found = false;
        if (isset($request->start_render_view) && $request->render_view_status == 1) {
            $start_date = $request->start_render_view;
            $end_date = $request->end_render_view;
        } else {
            $start_date = $request->start_month;
            $end_date = date('Y-m-t', strtotime($request->start_month));
        }
        $start_date = date('Y-m-d 00:00:00', strtotime($start_date));
        $end_date = date('Y-m-d 23:59:59', strtotime($end_date));
        $bookings = \App\Bookings::with('booking_payments')
            ->where([
                ['checkin_time', '>=', $start_date],
                ['checkin_time', '<=', $end_date],
            ])
            ->get();
        if ($bookings->count() > 0) {
            $record_found = true;
            foreach ($bookings as $booking) {
                if ($booking->type == 5) {
                    $name = 'Barcode type transaction';
                    $email = 'N/A';
                    $phone_number = 'N/A';
                    $vehicle_num = $booking->barcode;
                    $amount = isset($booking->booking_payments->amount) ? $booking->booking_payments->amount : 0;
                    $checkin_time = date('Y-m-d H:i:s', strtotime($booking->checkin_time));
                    $checkout_time = date('Y-m-d H:i:s', strtotime($booking->checkout_time));
                } else {
                    $name = $booking->first_name == 'Paid Vehicle' ? __('dashboard.paid_vehicle') : $booking->first_name . ' ' . $booking->last_name;
                    $email = $booking->email;
                    $phone_number = $booking->phone_number;
                    $vehicle_num = $booking->vehicle_num;
                    $amount = isset($booking->booking_payments->amount) ? $booking->booking_payments->amount : 0;
                    $checkin_time = date('Y-m-d H:i:s', strtotime($booking->checkin_time));
                    $checkout_time = date('Y-m-d H:i:s', strtotime($booking->checkout_time));
                }


                $response[] = array(
                    'title' => substr($name, 0, 20),
                    'popover_title' => substr($name, 0, 300) . '...',
                    'start' => $checkin_time,
                    'end' => $checkout_time,
                    'allDay' => false,
                    'editable' => false,
                    'backgroundColor' => '#0078bc',
                    'borderColor' => '#0078bc',
                    'color' => 'white',
                    'data' => array(
                        'name' => $name,
                        'email' => $email,
                        'phone_number' => $phone_number,
                        'vehicle_num' => $vehicle_num,
                        'amount' => $amount,
                        'checkin_time' => $checkin_time,
                        'checkout_time' => $checkout_time,
                    )
                );
            }
        }
        $bookings = \App\Bookings::with('booking_payments')
            ->whereIn('type', [2, 3, 6, 7])
            ->get();
        if ($bookings->count() > 0) {
            $record_found = true;
            foreach ($bookings as $booking) {
                if ($booking->type == 6 || $booking->type == 7) {
                    if (date('Y-m-d', strtotime($booking->checkout_time)) < date('Y-m-d')) {
                        continue;
                    }
                }
                $name = $booking->first_name == 'Paid Vehicle' ? __('dashboard.paid_vehicle') : $booking->first_name . ' ' . $booking->last_name;
                $email = $booking->email;
                $phone_number = $booking->phone_number;
                $vehicle_num = $booking->vehicle_num;
                $amount = isset($booking->booking_payments->amount) ? $booking->booking_payments->amount : 0;
                $checkin_time = date('Y-m-d H:i:s', strtotime(date('H:i:s', strtotime($booking->checkin_time))));
                $checkout_time = date('Y-m-d H:i:s', strtotime(date('H:i:s', strtotime($booking->checkout_time))));
                $response[] = array(
                    'title' => substr($name, 0, 20),
                    'popover_title' => substr($name, 0, 300) . '...',
                    'start' => $checkin_time,
                    'end' => $checkout_time,
                    'allDay' => false,
                    'editable' => false,
                    'backgroundColor' => '#0078bc',
                    'borderColor' => '#0078bc',
                    'color' => 'white',
                    'data' => array(
                        'name' => $name,
                        'email' => $email,
                        'phone_number' => $phone_number,
                        'vehicle_num' => $vehicle_num,
                        'amount' => $amount,
                        'checkin_time' => $checkin_time,
                        'checkout_time' => $checkout_time,
                    )
                );
            }
        }

        if (!$record_found) {
            $response['data'][] = array(
                'post_content' => 'No Data Available',
                'time' => ''
            );
        }
        return $response;
    }

    /**
     * Get Calendar Event Details
     * @param Request $request
     * @return type
     */
    public function calendar_event_details(Request $request)
    {
        $data = $request->data;
        ob_start();
?>
        <div class="modal-header">
            <button type="button" class="close" aria-hidden="true" data-dismiss="modal">x</button>
            <h4 class="modal-title">Booking Details</h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th style="padding:10px">Name</th>
                            <td class="text-left text-info" style="padding:10px 30px;">
                                <?php
                                if ($data['name'] == '' && $data['name'] == null) {
                                    echo 'N/A';
                                } else {
                                    echo $data['name'];
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="padding:10px">Email</th>
                            <td class="text-left text-info" style="padding:10px 30px;">
                                <?php
                                if ($data['email'] == '' && $data['email'] == null) {
                                    echo 'N/A';
                                } else {
                                    echo $data['email'];
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="padding:10px">Phone</th>
                            <td class="text-left text-info" style="padding:10px 30px;">
                                <?php
                                if ($data['phone_number'] == '' && $data['phone_number'] == null) {
                                    echo 'N/A';
                                } else {
                                    echo $data['phone_number'];
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="table table-bordered">
                    <thead>
                        <th>Vehicle</th>
                        <th>Amount</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $data['vehicle_num']; ?></td>
                            <td><?php echo number_format($data['amount'], 2, ',', '.') ?> &euro;</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($data['checkin_time'])); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($data['checkout_time'])); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
        $response = ob_get_clean();
        return $response;
    }

    /**
     * Get Camera Enabled Device
     * @param Request $request
     * @return type
     */
    public function get_camera_enabled_device()
    {
        $response = array();
        $devices = \App\LocationDevices::where('camera_enabled', 1)->get();
        if ($devices->count() > 0) {
            foreach ($devices as $device) {
                $data = array();
                $data['transactions'] = array();
                $data['details'] = $device;
                $data['type'] = 1;
                $attendant_transaction_images = \App\TransactionImages::where('device_id', $device->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                if ($attendant_transaction_images->count() > 0) {
                    foreach ($attendant_transaction_images as $image) {
                        $data['transactions'][] = $image;
                    }
                }
                $response[] = (object) $data;
            }
        }

        return (object) $response;
    }

    /**
     * Get Ticket Enabled Device
     * @param Request $request
     * @return type
     */
    public function get_ticket_enabled_device()
    {
        $response = array();
        $devices = \App\LocationDevices::where('available_device_id', [1, 2])->get();

        if ($devices->count() > 0) {
            foreach ($devices as $device) {
                $response[] = (object) array(
                    'type' => 2,
                    'details' => $device,
                );
            }
        }

        return (object) $response;
    }

    /**
     * Get Last 5 Transactions
     * @param Request $request
     * @return type
     */
    public function get_last_5_transactions_1($limit = null, $das_p = false)
    {
        $response = array();
        $attendant_transaction = \App\AttendantTransactions::orderBy('updated_at', 'desc');
        if ($limit != null) {
            $attendant_transaction = $attendant_transaction->limit(5);
        }
        $attendant_transaction = $attendant_transaction->get();
        if ($attendant_transaction->count() > 0) {
            foreach ($attendant_transaction as $transaction) {
                $data = array();
                $data['image'] = url('/plugins/images/logo_2.png');
                $image = \App\TransactionImages::where('transaction_id', $transaction->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($image) {
                    if ($image->image_path != null) {
                        $data['image'] = $image->image_path;
                    }
                }
                $data['entry_device'] = '--';
                $entry_transaction = \App\TransactionImages::where([
                    ['transaction_id', $transaction->id],
                    ['type', 'in']
                ])
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($entry_transaction) {
                    if ($entry_transaction->image_path != null) {
                        $data['image'] = $entry_transaction->image_path;
                    }
                    $entry_device = \App\LocationDevices::find($entry_transaction->device_id);
                    if ($entry_device) {
                        $data['entry_device'] = $entry_device->device_name;
                    }
                }
                $data['exit_device'] = '--';
                $exit_transaction = \App\TransactionImages::where([
                    ['transaction_id', $transaction->id],
                    ['type', 'out']
                ])
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($exit_transaction) {
                    if ($exit_transaction->image_path != null) {
                        $data['image'] = $exit_transaction->image_path;
                    }
                    $exit_device = \App\LocationDevices::find($exit_transaction->device_id);
                    if ($exit_device) {
                        $data['exit_device'] = $exit_device->device_name;
                    }
                }
                $attendant = \App\Attendants::find($transaction->attendant_id);
                if (!$attendant) {
                    continue;
                }
                if ($das_p) {
                    $booking_details = \App\Bookings::where('type', '=', 6)
                        ->where('id', $attendant->booking_id)
                        ->first();
                } else {
                    $booking_details = \App\Bookings::whereNotIn('type', [6, 7])
                        ->where('id', $attendant->booking_id)
                        ->first();
                }
                if (!$booking_details) {
                    continue;
                }
                $data['check_in'] = date('d/m/Y H:i', strtotime($transaction->check_in));
                $data['check_out'] = $transaction->check_out == null ? '--' : date('d/m/Y H:i', strtotime($transaction->check_out));
                if ($booking_details->vehicle_num != null) {
                    $data['vehicle'] = $booking_details->vehicle_num;
                } elseif ($booking_details->customer_vehicle_info_id != null) {
                    $vehicle = \App\CustomerVehicleInfo::find($booking_details->customer_vehicle_info_id);
                    if (!$vehicle) {
                        $data['vehicle'] = __('dashboard.paid_vehicle');
                    } else {
                        $data['vehicle'] = $vehicle->num_plate;
                    }
                } else {
                    $data['vehicle'] = __('dashboard.paid_vehicle');
                }
                $data['name'] = $booking_details->type == 5 ? $booking_details->barcode : $booking_details->first_name == 'Paid Vehicle' ? __('dashboard.paid_vehicle') : $booking_details->first_name . ' ' . $booking_details->last_name;
                $data['email'] = !empty($booking_details->email) ? $booking_details->email : 'N/A';
                if ($booking_details->booking_payments) {
                    $data['amount'] = $booking_details->booking_payments->amount;
                } else {
                    $data['amount'] = 'N/A';
                }
                $data['phone_number'] = $booking_details->phone_number == null ? 'N/A' : $booking_details->phone_number;
                $data['id'] = $booking_details->id;
                if ($booking_details->type == 1) {
                    $data['type'] = 'Send Ticket';
                } elseif ($booking_details->type == 2) {
                    $data['type'] = 'Whitelist';
                } elseif ($booking_details->type == 3) {
                    $data['type'] = 'Userlist';
                } elseif ($booking_details->type == 4) {
                    $data['type'] = 'Customer';
                } elseif ($booking_details->type == 5) {
                    $data['type'] = 'Barcode';
                } elseif ($booking_details->type == 6 || $booking_details->type == 7) {
                    $data['type'] = 'Tommy Reservation';
                } else {
                    $data['type'] = 'N/A';
                }
                if (empty($data['name'])) {
                    $data['name'] = 'Paid Person';
                }
                if (empty($data['vehicle'])) {
                    $data['vehicle'] = __('dashboard.paid_vehicle');
                }
                if ($booking_details->type == 0) {
                    $data['type'] = 'N/A';
                } elseif ($booking_details->type == 1) {
                    $data['type'] = 'Send Ticket';
                } elseif ($booking_details->type == 2) {
                    $data['type'] = 'White List';
                } elseif ($booking_details->type == 3) {
                    $data['type'] = 'User List';
                } elseif ($booking_details->type == 4) {
                    $data['type'] = 'Customer';
                } elseif ($booking_details->type == 5) {
                    $data['type'] = 'BarCode';
                } elseif ($booking_details->type == 6 || $booking_details->type == 7) {
                    $data['type'] = 'Person Ticket';
                } else {
                    $data['type'] = 'N/A';
                }
                $vehiclePaymentTransactions = \App\TransactionPaymentVehicles::with('location_devices')
                    ->where('booking_id', $attendant->booking_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                $data['vehicle_payment_transactions'] = $vehiclePaymentTransactions;
                $response[] = (object) $data;
            }
        }
        return $response;
    }

    public function get_last_5_transactions($limit = null, $das_p = false)
    {
        if (!$das_p) {
            $attendant_transaction = \App\LatestVehicleTransactions::all();
        } else {
            $attendant_transaction = \App\LatestPersonTransactions::all();
        }
        $response = array();
        if ($attendant_transaction->count() > 0) {
            foreach ($attendant_transaction as $transaction) {
                //                if (date('Y-m-d', strtotime($transaction->check_in)) != date('Y-m-d')) {
                //                    continue;
                //                }
                $data = array();
                $data['image'] = url('/plugins/images/logo_2.png');
                if (!$das_p) {
                    $image = \App\TransactionImages::where('transaction_id', $transaction->attendant_transaction_id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($image) {
                        if ($image->image_path != null) {
                            $data['image'] = $image->image_path;
                        }
                    }
                }
                $data['id'] = $transaction->attendant_transaction_id;
                $data['booking_id'] = $transaction->booking_id;
                $data['check_in'] = date('d/m/Y H:i', strtotime($transaction->check_in));
                $data['check_out'] = $transaction->check_out == null ? '--' : date('d/m/Y H:i', strtotime($transaction->check_out));
                if (!$das_p) {
                    $data['vehicle'] = $transaction->vehicle_num;
                }
                $response[] = (object) $data;
            }
        }
        return $response;
    }

    /**
     * Get Open Gate Status
     * @param Request $request
     * @return type
     */
    public function open_gate(Request $request)
    {
        try {
            $location = new \App\Http\Controllers\Settings\LocationSettings();
            $device_id = $request->device_id;
            $key = strtotime($location->get_location()->created_at) . '-' . $device_id;
            $location_devices = \App\LocationDevices::find($device_id);
            if (!$location_devices) {
                $message = 'Device is not available.';
                return json_encode(array('status' => 0, 'message' => $message));
            }
            if (!$location_devices->is_synched) {
                $message = 'Device is not synched.';
                return json_encode(array('status' => 0, 'message' => $message));
            }
            $open_gate_vehcile_num = $request->open_gate_vehcile_num;
            if ($open_gate_vehcile_num == '') {
                $message = __('dashboard.open_gate_vehcile_num_message');
                return json_encode(array('status' => 0, 'message' => $message));
            }
            $open_gate_reason = $request->open_gate_reason;
            if ($location_devices->device_direction == 'in') {
                $already_at_location = false;
                $booking = \App\Bookings::where('vehicle_num', $open_gate_vehcile_num)->orderBy('checkin_time', 'DESC')->first();
                if ($booking) {
                    $settings = new \App\Http\Controllers\Settings\Settings();
                    $at_location = $settings->is_booking_at_location($booking->id);
                    if ($at_location) {
                        $already_at_location = true;
                    }
                }
                if ($already_at_location) {
                    $message = 'Vehicle is Already on Location.';
                    return json_encode(array('status' => 3, 'message' => $message));
                } else {
                    //                $request->session()->put('open_gate_reason', $open_gate_reason);
                    Session::put('open_gate_reason', $open_gate_reason);
                    $VerifyVehicle = new \App\Http\Controllers\PlateReaderController\VerifyVehicle();
                    $verify_plate_num = $VerifyVehicle->verify_plate_num($request, $key, $device_id, $open_gate_vehcile_num, 100);
                    $location_devices->is_opened = 1;
                    $location_devices->save();
                    $message = 'Gate is opening';
                    return json_encode(array('status' => 1, 'message' => $message));
                }
            } elseif ($location_devices->device_direction == 'out') {
                $booking_valid = false;
                $booking = \App\Bookings::where('vehicle_num', $open_gate_vehcile_num)->orderBy('checkin_time', 'DESC')->first();
                if ($booking) {
                    $attendant = \App\Attendants::where('booking_id', $booking->id)->orderBy('created_at', 'DESC')->first();
                    if ($attendant) {
                        $attendant_transaction = \App\AttendantTransactions::where('attendant_id', $attendant->id)->orderBy('created_at', 'DESC')->first();
                        if ($attendant_transaction) {
                            if ($attendant_transaction->check_in != null && $attendant_transaction->check_out == null) {
                                $booking->checkout_time = date('Y-m-d H:i:s');
                                $booking->save();
                                //                                $attendant_transaction->check_out = date('Y-m-d H:i:s');
                                //                                $attendant_transaction->save();
                                //                                $transaction_images = new \App\TransactionImages();
                                //                                $transaction_images->device_id = $device_id;
                                //                                $transaction_images->transaction_id = $attendant_transaction->id;
                                //                                $transaction_images->type = 'out';
                                //                                $transaction_images->save();

                                $transaction_details = \App\TransactionPaymentVehicles::where('attendant_transaction_id', $attendant_transaction->id)->first();
                                if (!$transaction_details) {
                                    $transaction_details = new \App\TransactionPaymentVehicles();
                                    $transaction_details->device_id = $device_id;
                                    $transaction_details->booking_id = $booking->id;
                                    $transaction_details->attendant_transaction_id = $attendant_transaction->id;
                                    $transaction_details->status = 0;
                                    $transaction_details->amount = 0;
                                    $transaction_details->transaction = 'Manual Transaction';
                                    $transaction_details->save();
                                }

                                $manual_open_gate = new \App\OpenGateManualTransaction();
                                $manual_open_gate->transaction_payment_id = $transaction_details->id;
                                //$manual_open_gate->transaction_images_id = $transaction_images->id;
                                $manual_open_gate->attendant_transaction_id = $attendant_transaction->id;
                                $manual_open_gate->reason = $open_gate_reason;
                                if (\Illuminate\Support\Facades\Auth::check()) {
                                    $manual_open_gate->user_id = \Illuminate\Support\Facades\Auth::id();
                                } else {
                                    $manual_open_gate->user_id = 1;
                                }
                                $manual_open_gate->location_device_id = $device_id;
                                $manual_open_gate->save();
                                Artisan::call('command:OpenGateForExitVehcile', [
                                    'device' => $device_id, 'vehicle' => $open_gate_vehcile_num, 'message' => "Good Bye exit"
                                ]);
                                $location_devices->is_opened = 1;
                                $location_devices->save();
                                $message = 'Gate is opening';
                                return json_encode(array('status' => 1, 'message' => $message));
                            }
                        }
                    }
                }
                if (!$booking_valid) {
                    $message = 'Vehicle Number is Invalid. Are you sure you want to use this number';
                    return json_encode(array('status' => 2, 'message' => $message));
                }
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('open_gate', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);

            $message = 'Something went wrong';
            return json_encode(array('status' => 0, 'message' => $message));
        }
    }

    /**
     * No Entrance Transaction
     * @param Request $request
     * @return type
     */
    public function no_entrance_transaction(Request $request)
    {
        try {
            $location = new \App\Http\Controllers\Settings\LocationSettings();
            $device_id = $request->device_id;
            $key = strtotime($location->get_location()->created_at) . '-' . $device_id;
            $location_devices = \App\LocationDevices::find($device_id);
            if (!$location_devices) {
                $message = 'Device is not available.';
                return json_encode(array('status' => 0, 'message' => $message));
            }
            if (!$location_devices->is_synched) {
                $message = 'Device is not synched.';
                return json_encode(array('status' => 0, 'message' => $message));
            }
            $open_gate_vehcile_num = $request->open_gate_vehcile_num;
            $open_gate_reason = $request->open_gate_reason;
            if ($location_devices->device_direction == 'in') {
                //                $request->session()->put('open_gate_reason', $open_gate_reason);
                Session::put('open_gate_reason', $open_gate_reason);
                $VerifyVehicle = new \App\Http\Controllers\PlateReaderController\VerifyVehicle();
                $verify_plate_num = $VerifyVehicle->verify_plate_num($request, $key, $device_id, $open_gate_vehcile_num, 100);
                $location_devices->is_opened = 1;
                $location_devices->save();
                $message = 'Gate is opening';
                return json_encode(array('status' => 1, 'message' => $message));
            } elseif ($location_devices->device_direction == 'out') {
                $dataArray = array(
                    'first_name' => __('dashboard.paid_vehicle'),
                    'vehicle_num' => $open_gate_vehcile_num,
                    'type' => 8,
                    'checkin_time' => date('Y-m-d H:i:s'),
                    'checkout_time' => date('Y-m-d H:i:s'),
                    'amount' => 0,
                    'payment_id' => 'Paid Vehicle'
                );
                $booking = new \App\Bookings();
                $booking->type = $dataArray['type'];
                $booking->first_name = $dataArray['first_name'];
                $booking->vehicle_num = $dataArray['vehicle_num'];
                $booking->checkin_time = $dataArray['checkin_time'];
                $booking->checkout_time = $dataArray['checkout_time'];
                $booking->save();
                $bookingId = $booking->id;
                $booking_payment = new \App\BookingPayments();
                $booking_payment->booking_id = $bookingId;
                $booking_payment->amount = $dataArray['amount'];
                $booking_payment->payment_id = $dataArray['payment_id'];
                $booking_payment->checkin_time = $dataArray['checkin_time'];
                $booking_payment->save();
                $bookingPaymentId = $booking_payment->id;
                $user = \App\User::first();
                if ($user) {
                    $location_setting = \App\LocationOptions::first();
                    if ($location_setting) {
                        $key = $location_setting->live_id . '_' . $user->live_id;
                        $Key = base64_encode($key);
                    }
                }
                $http = new \GuzzleHttp\Client();
                $response = $http->post($this->url . '/api/store-booking-info', [
                    'form_params' => [
                        'token' => $Key,
                        'data' => $dataArray
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
                if ($responseData['success'] && array_key_exists('booking_info_live_id', $responseData['data'])) {
                    $booking = \App\Bookings::find($bookingId);
                    if ($booking) {
                        $booking->live_id = $responseData['data']['booking_info_live_id'];
                        $booking->save();
                    }
                }
                if ($responseData['success'] && array_key_exists('booking_payment_live_id', $responseData['data'])) {
                    $booking_payment = \App\BookingPayments::find($bookingPaymentId);
                    if ($booking_payment) {
                        $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                        $booking_payment->save();
                    }
                }
                $attendant = new \App\Attendants();
                $attendant->booking_id = $bookingId;
                $attendant->save();
                $attendant_id = $attendant->id;
                $attendant_transaction = new \App\AttendantTransactions();
                $attendant_transaction->attendant_id = $attendant_id;
                $attendant_transaction->check_in = date('Y-m-d H:i:s');
                //$attendant_transaction->check_out = date('Y-m-d H:i:s');
                $attendant_transaction->save();

                //            $transaction_images = new \App\TransactionImages();
                //            $transaction_images->device_id = $device_id;
                //            $transaction_images->transaction_id = $attendant_transaction->id;
                //            $transaction_images->type = 'in';
                //            $transaction_images->save();
                //                $transaction_images = new \App\TransactionImages();
                //                $transaction_images->device_id = $device_id;
                //                $transaction_images->transaction_id = $attendant_transaction->id;
                //                $transaction_images->type = 'out';
                //                $transaction_images->save();

                $transaction_details = new \App\TransactionPaymentVehicles();
                $transaction_details->device_id = $device_id;
                $transaction_details->booking_id = $bookingId;
                $transaction_details->attendant_transaction_id = $attendant_transaction->id;
                $transaction_details->status = 0;
                $transaction_details->amount = 0;
                $transaction_details->transaction = 'No Entrance Transaction';
                $transaction_details->save();
                //
                $manual_open_gate = new \App\OpenGateManualTransaction();
                $manual_open_gate->transaction_payment_id = $transaction_details->id;
                $manual_open_gate->attendant_transaction_id = $attendant_transaction->id;
                $manual_open_gate->reason = $open_gate_reason;
                if (\Illuminate\Support\Facades\Auth::check()) {
                    $manual_open_gate->user_id = \Illuminate\Support\Facades\Auth::id();
                } else {
                    $manual_open_gate->user_id = 1;
                }
                $manual_open_gate->location_device_id = $device_id;
                $manual_open_gate->save();

                $location_devices->is_opened = 1;
                $location_devices->save();
                $message = 'Gate is opening';
                Artisan::call('command:OpenGateForExitVehcile', [
                    'device' => $device_id, 'vehicle' => $open_gate_vehcile_num, 'message' => "Good Bye exit"
                ]);
                return json_encode(array('status' => 1, 'message' => $message));
            }
            //            Artisan::call('command:OpenTicketReader', [
            //                'device' => $request->device_id
            //            ]);
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('no_entrance_transaction', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);

            echo $ex->getMessage();
        }
    }

    /**
     * Get Close Gate Status
     * @param Request $request
     * @return type
     */
    public function close_gate(Request $request)
    {
        $response = 0;
        if (isset($request->device_id)) {
            Artisan::call('command:OpenTicketReader', [
                'device' => $request->device_id
            ]);
            $response = 1;
        }
        $message = 'Please wait process is in progress';
        if (\Illuminate\Support\Facades\Session::has('open_gate_message')) {
            $message = \Illuminate\Support\Facades\Session::get('open_gate_message');
            \Illuminate\Support\Facades\Session::forget('open_gate_message');
        }
        if (\Illuminate\Support\Facades\Session::has('open_gate_status')) {
            $response = \Illuminate\Support\Facades\Session::get('open_gate_status');
            \Illuminate\Support\Facades\Session::forget('open_gate_status');
        }
        return json_encode(array('status' => $response, 'message' => $message));
    }

    /**
     * Get Last 5 Transactions
     * @param Request $request
     * @return type
     */
    public function transactions(Request $request)
    {
        $get_last_5_transactions = $this->get_last_5_transactions();
        return view('transactions.index', [
            'get_last_5_transactions' => $get_last_5_transactions,
        ]);
    }

    /**
     * Get Transaction Details
     * @param Request $request
     * @return type
     */
    public function transaction_details_1(Request $request)
    {
        $response = array();
        $attendant_transaction = \App\AttendantTransactions::orderBy('updated_at', 'desc');
        $attendant_transaction = $attendant_transaction->paginate(5);
        if ($attendant_transaction->count() > 0) {
            foreach ($attendant_transaction as $transaction) {
                $data = array();
                $data['image'] = url('/plugins/images/logo_2.png');
                $image = \App\TransactionImages::where('transaction_id', $transaction->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($image) {
                    if ($image->image_path != null) {
                        $data['image'] = $image->image_path;
                    }
                }
                $data['entry_device'] = '--';
                $entry_transaction = \App\TransactionImages::where([
                    ['transaction_id', $transaction->id],
                    ['type', 'in']
                ])
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($entry_transaction) {
                    if ($entry_transaction->image_path != null) {
                        $data['image'] = $entry_transaction->image_path;
                    }
                    $entry_device = \App\LocationDevices::find($entry_transaction->device_id);
                    if ($entry_device) {
                        $data['entry_device'] = $entry_device->device_name;
                    }
                }
                $data['exit_device'] = '--';
                $exit_transaction = \App\TransactionImages::where([
                    ['transaction_id', $transaction->id],
                    ['type', 'out']
                ])
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($exit_transaction) {
                    if ($exit_transaction->image_path != null) {
                        $data['image'] = $exit_transaction->image_path;
                    }
                    $exit_device = \App\LocationDevices::find($exit_transaction->device_id);
                    if ($exit_device) {
                        $data['exit_device'] = $exit_device->device_name;
                    }
                }
                $attendant = \App\Attendants::find($transaction->attendant_id);
                if (!$attendant) {
                    continue;
                }
                $booking_details = \App\Bookings::find($attendant->booking_id);
                if (!$booking_details) {
                    continue;
                }
                $data['check_in'] = date('d/m/Y H:i', strtotime($transaction->check_in));
                $data['check_out'] = $transaction->check_out == null ? '--' : date('d/m/Y H:i', strtotime($transaction->check_out));
                if ($booking_details->vehicle_num != null) {
                    $data['vehicle'] = $booking_details->vehicle_num;
                } elseif ($booking_details->customer_vehicle_info_id != null) {
                    $vehicle = \App\CustomerVehicleInfo::find($booking_details->customer_vehicle_info_id);
                    if (!$vehicle) {
                        $data['vehicle'] = 'Paid Vehicle';
                    } else {
                        $data['vehicle'] = $vehicle->num_plate;
                    }
                } else {
                    $data['vehicle'] = 'Paid Vehicle';
                }
                $data['name'] = $booking_details->type == 5 ? $booking_details->barcode : $booking_details->first_name . ' ' . $booking_details->last_name;
                $data['email'] = !empty($booking_details->email) ? $booking_details->email : 'N/A';
                if ($booking_details->booking_payments) {
                    $data['amount'] = $booking_details->booking_payments->amount;
                } else {
                    $data['amount'] = 'N/A';
                }
                $data['phone_number'] = $booking_details->phone_number == null ? 'N/A' : $booking_details->phone_number;
                $data['id'] = $booking_details->id;
                if ($booking_details->type == 1) {
                    $data['type'] = 'Send Ticket';
                } elseif ($booking_details->type == 2) {
                    $data['type'] = 'Whitelist';
                } elseif ($booking_details->type == 3) {
                    $data['type'] = 'Userlist';
                } elseif ($booking_details->type == 4) {
                    $data['type'] = 'Customer';
                } elseif ($booking_details->type == 5) {
                    $data['type'] = 'Barcode';
                } elseif ($booking_details->type == 6 || $booking_details->type == 7) {
                    $data['type'] = 'Tommy Reservation';
                } else {
                    $data['type'] = 'N/A';
                }
                if (empty($data['name'])) {
                    $data['name'] = 'Paid Person';
                }
                if (empty($data['vehicle'])) {
                    $data['vehicle'] = 'Paid Vehicle';
                }
                if ($booking_details->type == 0) {
                    $data['type'] = 'N/A';
                } elseif ($booking_details->type == 1) {
                    $data['type'] = 'Send Ticket';
                } elseif ($booking_details->type == 2) {
                    $data['type'] = 'White List';
                } elseif ($booking_details->type == 3) {
                    $data['type'] = 'User List';
                } elseif ($booking_details->type == 4) {
                    $data['type'] = 'Customer';
                } elseif ($booking_details->type == 5) {
                    $data['type'] = 'BarCode';
                } elseif ($booking_details->type == 6 || $booking_details->type == 7) {
                    $data['type'] = 'Tommy Reservation';
                } else {
                    $data['type'] = 'N/A';
                }
                $vehiclePaymentTransactions = \App\TransactionPaymentVehicles::with('location_devices')
                    ->where('booking_id', $attendant->booking_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                $data['vehicle_payment_transactions'] = $vehiclePaymentTransactions;
                $response[] = (object) $data;
            }
        }
        return view('transactions.transaction_details', [
            'get_last_5_transactions' => $response,
            'transactions' => $attendant_transaction,
        ]);
    }

    /**
     * Get Vehicles Currently On Location
     * @param Request $request
     * @return type
     */
    public function currently_on_location(Request $request)
    {
        $at_location = $this->at_location_vehicles();
        return view('at_location.index', [
            'at_location' => $at_location,
        ]);
    }

    /**
     * Get Persons Currently On Location
     * @param Request $request
     * @return type
     */
    public function currently_on_location_persons(Request $request)
    {
        $at_location = $this->at_location_persons();
        return view('at_location.persons', [
            'at_location' => $at_location,
        ]);
    }

    /**
     * Get Attendants at Location
     * @param Request $request
     * @return type
     */
    public function at_location($limit = null)
    {
        $at_location = array();
        $transactions = \App\AttendantTransactions::whereNotNull('check_in')
            ->whereNull('check_out')
            ->orderBy('created_at', 'desc');
        if ($limit != null) {
            $transactions = $transactions->limit($limit);
        }
        $transactions = $transactions->get();

        if ($transactions->count() > 0) {
            foreach ($transactions as $transaction) {
                $attendant = \App\Attendants::find($transaction->attendant_id);
                if (!$attendant) {
                    continue;
                }
                $booking = \App\Bookings::with(
                    'booking_payments'
                )->find($attendant->booking_id);
                if (!$booking || $booking->type == 6 || $booking->type == 7) {
                    continue;
                }
                $data = array();
                $data['id'] = $booking->id;
                $name = false;
                if ($booking->type == 5) {
                    if ($booking->first_name != null) {
                        $name = $booking->first_name;
                    }
                    if ($booking->last_name != null) {
                        $name = $name . ' ' . $booking->last_name;
                    }
                    if (!$name) {
                        $name = $booking->barcode;
                    }
                } else {
                    if ($booking->first_name != null) {
                        $name = $booking->first_name;
                    }
                    if ($booking->last_name != null) {
                        $name = $name . ' ' . $booking->last_name;
                    }
                }
                if (!$name) {
                    $data['name'] = 'N/A';
                }
                $data['name'] = $name;
                $data['email'] = $booking->email;
                $data['phone_number'] = $booking->phone_number;
                if ($booking->vehicle_num != null) {
                    $data['vehicle_num'] = $booking->vehicle_num;
                } elseif ($booking->customer_vehicle_info_id != null) {
                    $vehicle = \App\CustomerVehicleInfo::find($booking->customer_vehicle_info_id);
                    if (!$vehicle) {
                        $data['vehicle_num'] = 'N/A';
                    } else {
                        $data['vehicle_num'] = $vehicle->num_plate;
                    }
                } else {
                    $data['vehicle_num'] = 'N/A';
                }
                $data['amount'] = 0;
                if ($booking->booking_payments) {
                    $data['amount'] = $booking->booking_payments->amount;
                } else {
                    $data['amount'] = 'N/A';
                }

                $data['checkin'] = date('d/m/Y H:i', strtotime($transaction->check_in));
                $at_location[] = (object) $data;
            }
        }
        return $at_location;
    }

    /**
     * Get Vehicles Attendants at Location
     * @param Request $request
     * @return type
     */
    public function at_location_vehicles($limit = null)
    {
        $at_location = array();
        $type = [1, 2, 3, 4, 5];
        $transactions = \App\AttendantTransactions::whereHas(
            'attendants.bookings',
            function ($query) {
                $query->whereIn('type', array(1, 2, 3, 4));
            }
        )->whereNull('check_out')->orderBy('created_at', 'desc');
        if ($limit != null) {
            $transactions = $transactions->limit($limit);
        }
        $transactions = $transactions->get();
        if ($transactions->count() > 0) {
            foreach ($transactions as $transaction) {
                $attendant = \App\Attendants::find($transaction->attendant_id);
                if (!$attendant) {
                    continue;
                }
                $booking = \App\Bookings::with(
                    'booking_payments'
                )->find($attendant->booking_id);
                if ($booking->type == 5 && $booking->vehicle_num == null) {
                    continue;
                }
                $data = array();
                $data['id'] = $booking->id;
                $name = false;
                if ($booking->type == 5) {
                    if ($booking->first_name != null) {
                        $name = $booking->first_name;
                    }
                    if ($booking->last_name != null) {
                        $name = $name . ' ' . $booking->last_name;
                    }
                    if (!$name) {
                        $name = $booking->barcode;
                    }
                } else {
                    if ($booking->first_name != null) {
                        $name = $booking->first_name;
                    }
                    if ($booking->last_name != null) {
                        $name = $name . ' ' . $booking->last_name;
                    }
                }
                if (!$name) {
                    $data['name'] = 'N/A';
                }
                $data['name'] = $name == 'Paid Vehicle' || empty($name) ? __('dashboard.paid_vehicle') : $name;
                $data['confidence'] = $booking->confidence;
                $data['low_confidence'] = $booking->low_confidence;
                $data['email'] = $booking->email ? $booking->email : 'N/A';
                $data['phone_number'] = $booking->phone_number ? $booking->phone_number : 'N/A';
                if ($booking->vehicle_num != null) {
                    $data['vehicle_num'] = $booking->vehicle_num;
                } elseif ($booking->customer_vehicle_info_id != null) {
                    $vehicle = \App\CustomerVehicleInfo::find($booking->customer_vehicle_info_id);
                    if (!$vehicle) {
                        $data['vehicle_num'] = 'N/A';
                    } else {
                        $data['vehicle_num'] = $vehicle->num_plate;
                    }
                } else {
                    $data['vehicle_num'] = 'N/A';
                }
                $data['amount'] = 0;
                if ($booking->booking_payments) {
                    $data['amount'] = $booking->booking_payments->amount;
                } else {
                    $data['amount'] = 'N/A';
                }

                $data['checkin'] = date('d/m/Y H:i', strtotime($transaction->check_in));
                $data['checkout'] = ' -- ';
                if ($booking->is_paid) {
                    if ($booking->booking_payments && $booking->booking_payments->amount > 0) {
                        $data['checkout'] = date('d/m/Y H:i', strtotime($booking->booking_payments->checkout_time));
                    }
                }
                $at_location[] = (object) $data;
            }
        }
        return $at_location;
    }

    /**
     * Get Persons Attendants at Location
     * @param Request $request
     * @return type
     */
    public function at_location_persons($limit = null)
    {
        $at_location = array();
        $transactions = \App\AttendantTransactions::whereHas(
            'attendants.bookings',
            function ($query) {
                $query->whereIn('type', array(5, 6));
            }
        )->whereNotNull('check_in')
            ->whereNull('check_out')
            ->orderBy('created_at', 'desc');
        if ($limit != null) {
            $transactions = $transactions->limit($limit);
        }
        $transactions = $transactions->get();
        if ($transactions->count() > 0) {
            foreach ($transactions as $transaction) {
                $attendant = \App\Attendants::find($transaction->attendant_id);
                $booking = \App\Bookings::with(
                    'booking_payments'
                )->find($attendant->booking_id);
                if (!empty($booking->vehicle_num)) {
                    continue;
                }
                $data = array();
                $data['id'] = $booking->id;
                $data['name'] = $booking->first_name . ' ' . $booking->last_name;
                if (empty(trim($data['name']))) {
                    $data['name'] = __('dashboard.paid_person');
                }
                $data['email'] = $booking->email ? $booking->email : 'N/A';
                $data['phone_number'] = $booking->phone_number ? $booking->phone_number : 'N/A';
                $data['vehicle_num'] = 'N/A';
                $data['amount'] = 0;
                if ($booking->booking_payments) {
                    $data['amount'] = $booking->booking_payments->amount;
                } else {
                    $data['amount'] = 'N/A';
                }
                $data['checkin'] = date('d/m/Y H:i', strtotime($transaction->check_in));
                $at_location[] = (object) $data;
            }
        }
        return $at_location;
    }

    /**
     * Get Devices Actions
     * @param Request $request
     * @return type
     */
    public function devices_actions(Request $request)
    {
        $camera_devices = $this->get_camera_enabled_device();
        $ticket_enabled_device = $this->get_ticket_enabled_device();
        $dashboard_devices = array();
        if ($camera_devices) {
            foreach ($camera_devices as $camera_device) {
                $dashboard_devices[] = $camera_device;
            }
        }
        if ($ticket_enabled_device) {
            foreach ($ticket_enabled_device as $ticket_device) {
                $dashboard_devices[] = $ticket_device;
            }
        }
        return view('devices.devices_actions', [
            'dashboard_devices' => $dashboard_devices,
        ]);
    }

    /**
     * Edit Vehicle Number
     * @param Request $request
     * @return type
     */
    public function edit_vehicle_num(Request $request)
    {
        $booking_id = $request->booking_id;
        $booking_Details = \App\Bookings::find($booking_id);
        if (!$booking_Details) {
            return array(
                'is_success' => 0,
                'response_html' => 0
            );
            //            return json_encode(array(
            //                'is_success' => 0,
            //                'response_html' => 0
            //            ));
        }

        ob_start();
    ?>
        <div class="modal-header">
            <button type="button" class="close" aria-hidden="true" data-dismiss="modal">x</button>
            <h4 class="modal-title">Booking Details</h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-bordered update_vehicle_form">
                    <tbody>
                        <?php
                        if ($booking_Details->image_path) :
                        ?>
                            <tr>
                                <td class="text-left text-info" style="padding:0px;" colspan="2">
                                    <img src="<?php echo $booking_Details->image_path; ?>" width="566">
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th style="padding:10px">Confidence</th>
                            <td class="text-left text-info" style="padding:10px 30px;">
                                <?php echo $booking_Details->confidence; ?></td>
                        </tr>
                        <?php if (!$booking_Details->low_confidence) : ?>
                            <tr>
                                <th style="padding:10px">Vehicle</th>
                                <td class="text-left text-info" style="padding:10px 30px;">
                                    <?php echo $booking_Details->vehicle_num; ?></td>
                            </tr>
                        <?php else : ?>

                            <tr>

                                <th style="padding:10px">Vehicle</th>
                                <td class="text-left text-info" style="padding:10px 30px;"><input typ="text" class="form-control vehicle_num" name="vehicle_num" value="<?php echo $booking_Details->vehicle_num; ?>"></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($booking_Details->low_confidence) : ?>
                    <div class="col-md-12 pl-0 pr-0">
                        <form class="hidden updated_vehicle_num_form" action="<?php echo url('dashboard/update_vehicle') ?>" method="POST">
                            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="booking_id" class="booking_id" value="<?php echo $booking_Details->id; ?>">
                            <input type="hidden" name="updated_vehicle_num" class="updated_vehicle_num">
                        </form>
                        <button type="button" onclick="update_vehicle_number()" class="btn btn-primary pull-right">Update</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
<?php
        $response = ob_get_clean();
        return array(
            'is_success' => 1,
            'response_html' => $response
        );
        //        return json_encode(array(
        //            'is_success' => 1,
        //            'response_html' => $response
        //        ));
    }

    /**
     * Update Vehicle Booking
     * @param Request $request
     * @return type
     */
    public function update_vehicle_booking(Request $request)
    {
        $booking_id = $request->booking_id;
        $updated_vehicle_num = $request->updated_vehicle_num;
        $booking_details = \App\Bookings::find($booking_id);
        if ($booking_details) {
            $booking_details->vehicle_num = $updated_vehicle_num;
            $booking_details->low_confidence = 1;
            $booking_details->save();
            Session::flash('heading', 'Success!');
            Session::flash('message', 'Vehicel number updated successfully');
            Session::flash('icon', 'success');
            return redirect('/dashboard');
        }
        Session::flash('heading', 'Error!');
        Session::flash('message', 'Vehicel number not updated. Please try again');
        Session::flash('icon', 'error');
        return redirect('/dashboard');
    }

    /**
     * Edit Device Vehicle Number
     * @param Request $request
     * @return type
     */
    public function edit_device_vehicle_num(Request $request)
    {
    }

    /**
     * Get Details of Low Confidence Vehicle Number
     * @param Request $request
     * @return type
     */
    public function low_confience_details($id)
    {
        $device_booking_details = \App\DeviceBookings::find($id);
        return view('transactions.low_confidence_vehicle', [
            'data' => $device_booking_details
        ]);
    }

    /**
     * Update Device Vehicle Number
     * @param Request $request
     * @return type
     */
    public function update_device_vehicle(Request $request)
    {
        $device_booking_id = $request->device_booking_id;
        $updated_vehicle_num = $request->updated_device_vehicle_num;
        $device_booking_details = \App\DeviceBookings::find($device_booking_id);
        if ($device_booking_details) {
            $device_booking_details->vehicle_num = $updated_vehicle_num;
            $device_booking_details->confidence = 100;
            $device_booking_details->save();
            $device_details = \App\LocationDevices::find($device_booking_details->device_id);
            $verify_vehicle = new PlateReaderController\VerifyVehicle();
            $open_gate = $verify_vehicle->open_gate_plate_reader($device_details, $updated_vehicle_num, '', 'entry');
            if (!$open_gate) {
                $message = 'Ticket Reader is not configured correctly';
                Session::flash('heading', 'Error!');
                Session::flash('message', $message);
                Session::flash('icon', 'error');
                return redirect('/dashboard');
            }
            Session::flash('heading', 'Success!');
            Session::flash('message', 'Gate is Opening for vehicle.');
            Session::flash('icon', 'success');
            return redirect('/dashboard');
        }
        Session::flash('heading', 'Error!');
        Session::flash('message', 'Vehicel number not updated. Please try again');
        Session::flash('icon', 'error');
        return redirect('/dashboard');
    }

    /**
     * Check Vehicle Number
     * @param Request $request
     * @return type
     */
    public function check_vehicle_number($num1, $num2, $char_to_match)
    {
    }

    /**
     * Get Devices(Function Not Used)
     * @param Request $request
     * @return type
     */
    public function get_devices_p($das_p = false)
    {
        $ticket_readers = array();
        $person_ticket_readers = array();
        $plate_readers = array();
        $payment_terminal = array();
        $devices = \App\LocationDevices::whereNotIn('available_device_id', [4, 5])
            ->orderBy('vehicle_device_sorting', 'asc')
            ->get();
        if ($das_p) {
            $devices = \App\LocationDevices::whereNotIn('available_device_id', [4, 5])
                ->orderBy('person_device_sorting', 'asc')
                ->get();
        }
        if ($devices->count() > 0) {
            foreach ($devices as $device) {
                if ($device->available_device_id == 1) {
                    if ($device->is_synched) {
                        $status = 'Connected';
                        $status_color = '';
                    } else {
                        $status = 'Out of Order';
                        $status_color = '#dc3545';
                    }
                    $title = $device->device_name;
                    $id = $device->id;
                    $transactions = $this->get_device_transactions($device);
                    $ticket_readers[] = array(
                        'name' => $title,
                        'id' => $id,
                        'direction' => $device->device_direction,
                        'is_synched' => $device->is_synched,
                        'is_opened' => $device->is_opened,
                        'status' => $status,
                        'status_color' => $status_color,
                        'transactions' => $transactions,
                    );
                } elseif ($device->available_device_id == 2) {
                    if ($device->is_synched) {
                        $status = 'Connected';
                        $status_color = '';
                    } else {
                        $status = 'Out of Order';
                        $status_color = '#dc3545';
                    }
                    $title = $device->device_name;
                    $id = $device->id;
                    $transactions = $this->get_device_transactions($device);
                    $person_ticket_readers[] = array(
                        'name' => $title,
                        'id' => $id,
                        'is_synched' => $device->is_synched,
                        'status' => $status,
                        'status_color' => $status_color,
                        'transactions' => $transactions,
                    );
                } elseif ($device->available_device_id == 3) {
                    if ($device->is_synched) {
                        $status = 'Connected';
                        $status_color = '';
                    } else {
                        $status = 'Out of Order';
                        $status_color = '#dc3545';
                    }
                    $title = $device->device_name;
                    $id = $device->id;
                    $transactions = $this->get_device_transactions($device);
                    $plate_readers[] = array(
                        'name' => $title,
                        'id' => $id,
                        'is_synched' => $device->is_synched,
                        'status' => $status,
                        'status_color' => $status_color,
                        'transactions' => $transactions,
                    );
                } elseif ($device->available_device_id == 6) {
                    if ($device->is_synched) {
                        $status = 'Connected';
                        $status_color = '';
                    } else {
                        $status = 'Out of Order';
                        $status_color = '#dc3545';
                    }
                    $title = $device->device_name;
                    $id = $device->id;
                    $vehicle_transactions = $this->get_vehicle_payment_transactions($device);
                    $person_transactions = $this->get_person_payment_transactions($device);
                    $payment_terminal[] = array(
                        'name' => $title,
                        'id' => $id,
                        'is_synched' => $device->is_synched,
                        'status' => $status,
                        'status_color' => $status_color,
                        'vehicle_transactions' => $vehicle_transactions,
                        'person_transactions' => $person_transactions,
                    );
                }
            }
        }
        if ($das_p) {
            return array(
                'person_ticket_readers' => $person_ticket_readers,
                'payment_terminal' => $payment_terminal,
            );
        } else {
            return array(
                'ticket_readers' => $ticket_readers,
                'person_ticket_readers' => $person_ticket_readers,
                'plate_readers' => $plate_readers,
                'payment_terminal' => $payment_terminal,
            );
        }
    }

    /**
     * Get Devices(Used)
     * @param Request $request
     * @return type
     */
    public function get_devices($das_p = false)
    {
        $allDevices = array();
        $devices = \App\LocationDevices::whereNotIn('available_device_id', [4, 5])
            ->orderBy('vehicle_device_sorting', 'asc')
            ->get();
        if ($das_p) {
            $devices = \App\LocationDevices::whereNotIn('available_device_id', [4, 5])
                ->orderBy('person_device_sorting', 'asc')
                ->get();
        }
        if ($devices->count() > 0) {
            foreach ($devices as $device) {
                if ($device->is_synched) {
                    $status = 'Connected';
                    $status_color = '';
                } else {
                    $status = 'Out of Order';
                    $status_color = '#dc3545';
                }
                $title = $device->device_name;
                $id = $device->id;
                $transactions = $this->get_device_transactions($device);
                if ($das_p) {
                    $transactions = $this->get_device_transactions($device, true);
                }
                $vehicle_transactions = array();
                $person_transactions = array();
                $related_switch = '';
                if ($device->available_device_id == 6) {
                    $vehicle_transactions = $this->get_vehicle_payment_transactions($device);
                    $person_transactions = $this->get_person_payment_transactions($device);
                    $get_related_switch = DeviceTicketReaders::where('device_id', $device->id)
                        ->first();
                    if ($get_related_switch) {
                        $related_switch = $get_related_switch->ticket_reader_id;
                    }
                }
                $allDevices[] = array(
                    'name' => $title,
                    'id' => $id,
                    'direction' => $device->device_direction,
                    'is_synched' => $device->is_synched,
                    'is_opened' => $device->is_opened,
                    'status' => $status,
                    'status_color' => $status_color,
                    'transactions' => $transactions,
                    'available_device_id' => $device->available_device_id,
                    'has_related_ticket_reader' => $device->has_related_ticket_reader,
                    'vehicle_transactions' => $vehicle_transactions,
                    'person_transactions' => $person_transactions,
                    'has_gate' => $device->has_gate,
                    'barrier_status' => $device->barrier_status,
                    'has_always_access' => $device->has_always_access,
                    'open_relay' => isset($device->open_relay) ? $device->open_relay : null,
                    'close_relay' => isset($device->close_relay) ? $device->close_relay : null,
                    'related_switch' => $related_switch
                );
            }
        }
        return $allDevices;
    }

    /**
     * Get Device Transactions
     * @param Request $request
     * @return type
     */
    public function get_device_transactions($device, $das_p = false, $limit = 5)
    {
        $attendant_transactions = \App\TransactionImages::where('device_id', $device->id)
            ->orderBy('created_at', 'desc');
        if ($limit != null) {
            $attendant_transactions = $attendant_transactions->limit($limit);
        }
        $attendant_transactions = $attendant_transactions->get();
        $transactions = array();
        if ($attendant_transactions->count() > 0) {
            foreach ($attendant_transactions as $attendant_transaction) {
                $transaction_details = \App\AttendantTransactions::find($attendant_transaction->transaction_id);
                if (!$transaction_details) {
                    continue;
                }
                $attendants = \App\Attendants::find($transaction_details->attendant_id);
                if (!$attendants) {
                    continue;
                }
                $booking_details = \App\Bookings::find($attendants->booking_id);
                if (!$booking_details) {
                    continue;
                }
                //                elseif($das_p && $booking_details->type != 6){
                //                    continue;
                //                }
                //                elseif(!$das_p && $booking_details->type == 6){
                //                    continue;
                //                }
                $content = '';
                if (!empty($booking_details->vehicle_num)) {
                    $content = $booking_details->vehicle_num . ' - ' . date('d/m/Y H:i', strtotime($attendant_transaction->updated_at));
                } elseif (!empty($booking_details->first_name)) {
                    $content = $booking_details->first_name . ' ' . $booking_details->last_name . ' - ' . date('d/m/Y H:i', strtotime($attendant_transaction->updated_at));
                }
                $transactions[] = array(
                    'vehicle' => $booking_details->vehicle_num,
                    'name' => $booking_details->first_name . ' ' . $booking_details->last_name,
                    'booking_id' => $booking_details->id,
                    'id' => $attendant_transaction->id,
                    'type' => $attendant_transaction->type,
                    'content' => $content,
                    'image_path' => $attendant_transaction->image_path != null ? $attendant_transaction->image_path : url('/plugins/images/logo_2.png'),
                    'time' => date('d/m/Y H:i', strtotime($attendant_transaction->updated_at)),
                );
            }
        }
        return $transactions;
    }

    /**
     * Get Vehicle Payment Transactions
     * @param Request $request
     * @return type
     */
    public function get_vehicle_payment_transactions($device, $limit = 5)
    {
        $vehcile_payment_transactions = \App\TransactionPaymentVehicles::where('device_id', $device->id)
            ->orderBy('created_at', 'desc');
        if ($limit != null) {
            $vehcile_payment_transactions = $vehcile_payment_transactions->limit($limit);
        }
        $vehcile_payment_transactions = $vehcile_payment_transactions->get();
        $transactions = array();
        if ($vehcile_payment_transactions->count() > 0) {
            foreach ($vehcile_payment_transactions as $vehcile_payment_transaction) {
                $transaction_details = \App\AttendantTransactions::find($vehcile_payment_transaction->attendant_transaction_id);
                if (!$transaction_details) {
                    continue;
                }
                $attendants = \App\Attendants::find($transaction_details->attendant_id);
                if (!$attendants) {
                    continue;
                }
                $booking_details = \App\Bookings::find($attendants->booking_id);
                if (!$booking_details) {
                    continue;
                }
                $status = 'unknown';
                if ($vehcile_payment_transaction->status == 0) {
                    $status = 'Failed';
                } elseif ($vehcile_payment_transaction->status == 1) {
                    $status = 'Success';
                } elseif ($vehcile_payment_transaction->status == 2) {
                    $status = 'Unknown';
                }
                $content = '';
                if (!empty($booking_details->vehicle_num)) {
                    $content = $booking_details->vehicle_num . ' - ' . date('d/m/Y H:i', strtotime($vehcile_payment_transaction->updated_at)) . ' - ' . $status;
                } elseif (!empty($booking_details->first_name)) {
                    $content = $booking_details->first_name . ' ' . $booking_details->last_name . ' - ' . date('d/m/Y H:i', strtotime($vehcile_payment_transaction->updated_at)) . ' - ' . $status;
                }
                $transactions[] = array(
                    'vehicle' => $booking_details->vehicle_num,
                    'name' => $booking_details->first_name . ' ' . $booking_details->last_name,
                    'booking_id' => $booking_details->id,
                    'type' => $status,
                    'id' => $vehcile_payment_transaction->id,
                    'content' => $content,
                    'amount' => $vehcile_payment_transaction->amount,
                    'time' => date('d/m/Y H:i', strtotime($vehcile_payment_transaction->updated_at)),
                );
            }
        }
        return $transactions;
    }

    /**
     * Get Person Payment Transactions
     * @param Request $request
     * @return type
     */
    public function get_person_payment_transactions($device, $limit = 5)
    {
        $person_payment_transactions = \App\TransactionPaymentPersons::where('device_id', $device->id)
            ->orderBy('created_at', 'desc');
        if ($limit != null) {
            $person_payment_transactions = $person_payment_transactions->limit($limit);
        }
        $person_payment_transactions = $person_payment_transactions->get();
        $transactions = array();
        if ($person_payment_transactions->count() > 0) {
            foreach ($person_payment_transactions as $person_payment_transaction) {
                $status = 'unknown';
                if ($person_payment_transaction->status == 0) {
                    $status = 'Failed';
                } elseif ($person_payment_transaction->status == 1) {
                    $status = 'Success';
                } elseif ($person_payment_transaction->status == 2) {
                    $status = 'Unknown';
                }
                $amount = 'N/A';
                $quantity = $person_payment_transaction->quantity;
                if (is_numeric($quantity)) {
                    $person_ticket_price = \App\Products::where('type', 'person_ticket')->first();
                    if ($person_ticket_price) {
                        $amount = $quantity * $person_ticket_price->price;
                    }
                }
                $content = $quantity . ' Persons - ' . date('d/m/Y H:i', strtotime($person_payment_transaction->updated_at)) . ' - ' . $status;
                //                $content = $quantity . ' Persons - ' . date('d/m/Y H:i', strtotime($person_payment_transaction->updated_at)) . ' - ' . $status;

                $transactions[] = array(
                    'type' => $status,
                    'amount' => $amount,
                    'id' => $person_payment_transaction->id,
                    'content' => $content,
                    'quantity' => $quantity,
                    'time' => date('d/m/Y H:i', strtotime($person_payment_transaction->updated_at)),
                );
            }
        }
        return $transactions;
    }

    /**
     * Get Widget 1 Details
     * @param Request $request
     * @return type
     */
    public function get_widget1_details($das_p = false, $recall = false)
    {
        $total_spots = 0;
        $total_spots_person = 0;
        $available_spots = 0;
        $total_bookings = 0;
        $not_checked_in_bookings = 0;
        $todayParkingSpots = 0;
        $todayPersonSpots = 0;
        $unexpected_bookings = 0;
        $expected_bookings = 0;
		$arrival_left = 0;

        $location_options = \App\LocationOptions::first();
        $locationExtraSpots = \App\LocationExtraSpots::where('date', date('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();
        if ($locationExtraSpots) {
            if (!empty($locationExtraSpots->avaialable_spots)) {
                $todayParkingSpots = $locationExtraSpots->avaialable_spots - $location_options->online_booking_stop_parking;
                $total_spots = $locationExtraSpots->avaialable_spots;
            }
            if (!empty($locationExtraSpots->person_avaialable_spots)) {
                $todayPersonSpots = $locationExtraSpots->person_avaialable_spots - $location_options->online_booking_stop_person;
                $total_spots_person = $locationExtraSpots->person_avaialable_spots;
            }
        }
        else{
            $total_spots = $location_options->total_spots;
            $total_spots_person = $location_options->total_spots_person;
            $todayParkingSpots = $total_spots - $location_options->online_booking_stop_parking;
            $todayPersonSpots = $total_spots_person - $location_options->online_booking_stop_person;
        }

        if ($das_p) {
            $type = 6;
            $is_online = 1;
           $on_location_bookings = \App\AttendantTransactions::whereHas(
                'attendants.bookings',
                function ($query) {
                    $query->whereIn('type', array(6))
                        ->where(function ($subQuery) {
                            $subQuery->whereNull('vehicle_num')
                                ->orWhere('vehicle_num', '')
                                ->orWhere('vehicle_num', 0);
                        })
                        ->where('is_tommy_online', 0)->whereNull('tommy_parent_id')->whereNull('tommy_childeren_id');
                }
            )->whereDate('check_in', '=', Carbon::today()->toDateString())->whereNull('check_out')->count();
            
            $on_location = $on_location_bookings;
            $arrivals = \App\AttendantTransactions::whereHas(
                'attendants.bookings',
                function ($query) use ($type) {
                    $query->where('type', $type)->where('is_tommy_online', 0)->whereNull('tommy_parent_id')->whereNull('tommy_childeren_id');
                }
            )->with([
                'attendants.bookings' =>
                function ($query) use ($type) {
                    $query->where('type', $type);
                }
            ])->whereDate('check_in', '=', Carbon::today()->toDateString())
                ->whereNotNull('check_out')
                ->count();

            $checked_in_person_bookings = \App\Bookings::whereHas('attendant_transactions')
                ->whereIn('type', array(6))->where('is_tommy_online', 0)->whereNull('tommy_childeren_id')->where('checkout_time', '=', date('Y-m-d 23:59:59'))->pluck('id')->all();

            $not_checked_in_bookings = \App\Bookings::whereNotIn('id', $checked_in_person_bookings)->where('checkout_time', '=', date('Y-m-d 23:59:59'))->where('type', 6)->where('is_tommy_online', 0)->whereNull('tommy_childeren_id')->count();
			$arrival_d = count($checked_in_person_bookings);
            $expected_bookings = count($checked_in_person_bookings) + $not_checked_in_bookings;
			$arrival_left = $expected_bookings - $arrival_d;
            $total_bookings = $not_checked_in_bookings + $arrivals;
            $available_spots = $total_spots_person - ($on_location);
            $total_spots = $total_spots_person;
            $todayPersonSpots = $todayPersonSpots -  $on_location;
            $data_array = (object) array(
                'on_location' => $on_location,
                'arrivals' => $arrivals,
                'expected_bookings' => $expected_bookings,
                'arrival_d' => $arrival_d,
                'online_person_spot' => $todayPersonSpots,
                'arrival_left' => $arrival_left,
            );
            \Illuminate\Support\Facades\Session::put('widget_1_booking_details_p', $data_array);
            if ($recall) {
                return;
            }
        } else {
            $type = [1, 2, 3, 4, 5];
            $on_location = \App\AttendantTransactions::whereHas(
                'attendants.bookings',
                function ($query) use ($type) {
                    $query->whereIn('type', $type)->whereNotNull('vehicle_num');
                }
            )->whereNull('check_out')->whereDate('check_in', '=', Carbon::today()->toDateString())
                ->count();
            $arrivals = \App\AttendantTransactions::whereHas(
                'attendants.bookings',
                function ($query) use ($type) {
                    $query->whereIn('type', $type);
                }
            )->whereDate('check_in', '=', Carbon::today()->toDateString())
                ->whereNotNull('check_out')
                ->count();
            $checked_in_known_bookings_parking = \App\Bookings::whereHas('attendant_transactions')
                ->where('type', 4)->where('checkout_time', date('Y-m-d 23:59:59'))->count();
            //$not_checked_in_bookings = \App\Bookings::whereNotIn('id', $checked_in_known_bookings_parking)->where('checkout_time', '=', date('Y-m-d 23:59:59'))->where('type', 4)->count();
			
            //            $total_bookings = $on_location + $arrivals;
            $total_bookings  = \App\Bookings::where('type', 4)
                            //->where('checkin_time', date('Y-m-d 00:00:00'))
                            ->where('checkout_time', date('Y-m-d 23:59:59'))
                            ->count();
			$expected_bookings =  $total_bookings - $checked_in_known_bookings_parking;
			$arrival_d = $checked_in_known_bookings_parking;
			$bookings_onlocation = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                    $query->whereNull('check_out');
                })->where('checkout_time', '=', date('Y-m-d 23:59:59'))->where('type', 4)->count();
            $todayParkingSpots = $todayParkingSpots - $total_bookings - ($on_location - $bookings_onlocation);
			$arrival_left = $expected_bookings;
            $data_array = (object) array(
                'on_location' => $on_location,
                'arrivals' => $arrivals,
                'expected_bookings' => $expected_bookings,
            );
            \Illuminate\Support\Facades\Session::put('widget_1_booking_details', $data_array);
            if ($recall) {
                return;
            }

            $available_spots = $total_spots - $on_location;
        }
        return (object) array(
            'total_spots' => $total_spots,
            'available_spots' => $available_spots < 0 ? 0 : $available_spots,
            'total_bookings' => $total_bookings,
            'arrival_d' => $arrival_d,
            'unexpected_bookings' => $unexpected_bookings,
            'expected_bookings' => $expected_bookings,
            'arrival_left' => $arrival_left,
            'online_parking_spot' => $todayParkingSpots < 0 ? 0 : $todayParkingSpots,
            'online_person_spot' => $todayPersonSpots  < 0 ? 0 : $todayPersonSpots,
        );
    }

    /**
     * Get Widget 2 Details
     * @param Request $request
     * @return type
     */
    public function get_widget2_details($das_p = false)
    {
        $expected = 0;
        $arrivals = 0;
        $on_location = 0;
        $booking_left = 0;
        $total = 0;
        if ($das_p) {
			if (!\Illuminate\Support\Facades\Session::has('widget_1_booking_details_p')) {
                $this->get_widget1_details(true, true);
            }
            $booking_details = \Illuminate\Support\Facades\Session::get('widget_1_booking_details_p');
            if ($booking_details) {
                $on_location = $booking_details->on_location;
                $total = $booking_details->expected_bookings;
            }
			$booking_left = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                    $query->whereNotNull('check_out');
                })->where('checkout_time', '=', date('Y-m-d 23:59:59'))->where('type', 6)->where('is_tommy_online', 0)->whereNull('tommy_childeren_id')->count();
        } else {
            if (!\Illuminate\Support\Facades\Session::has('widget_1_booking_details')) {
                $this->get_widget1_details(false, true);
            }
            $booking_details = \Illuminate\Support\Facades\Session::get('widget_1_booking_details');
            if ($booking_details) {
                $on_location = $booking_details->on_location;
                $arrivals = $booking_details->arrivals;
                $expected = $booking_details->expected_bookings;
            }
			$booking_left = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                    $query->whereNotNull('check_out');
                })->where('checkout_time', '=', date('Y-m-d 23:59:59'))->where('type', 4)->count();
            $total = $on_location + $arrivals;
        }
        return (object) array(
            'on_location' => $on_location,
            'total' => $total,
            'expected' => $expected,
            'arrivals' => $arrivals,
            'booking_left' => $booking_left
        );
    }

    /**
     * Get Widget 3 Details
     * @param Request $request
     * @return type
     */
    public function get_widget3_details()
    {
        $device_alerts = \App\DeviceAlerts::with('location_devices')
            ->orderBy('created_at', 'desc')
            ->orderBy('status', 'asc')
            ->limit(4)
            ->get();
        return (object) array(
            'device_alerts' => $device_alerts
        );
    }

    public function open_gate_person(Request $request)
    {
        $response = 0;
        if (isset($request->device_id)) {
            $location_device = \App\LocationDevices::find($request->device_id);
            if ($location_device->barrier_status < 1) {
                Artisan::call('command:OpenTicketReader', [
                    'device' => $request->device_id,
                    'vehicle' => ''
                ]);
            } else {
                Artisan::call('command:UnlockBarrier', [
                    'device' => $request->device_id
                ]);
            }
            $location_device->user_id = Auth::id();
            $location_device->barrier_status = 0;
            $location_device->save();
            $response = 1;
        }
        if (\Illuminate\Support\Facades\Auth::check()) {
            \Illuminate\Support\Facades\Session::forget('open_gate_by', \Illuminate\Support\Facades\Auth::id());
        }
        $message = 'Please wait process is in progress';
        if (\Illuminate\Support\Facades\Session::has('open_gate_message')) {
            $message = \Illuminate\Support\Facades\Session::get('open_gate_message');
            \Illuminate\Support\Facades\Session::forget('open_gate_message');
        }
        if (\Illuminate\Support\Facades\Session::has('open_gate_status')) {
            $response = \Illuminate\Support\Facades\Session::get('open_gate_status');
            \Illuminate\Support\Facades\Session::forget('open_gate_status');
        }
        return json_encode(array('status' => $response, 'message' => $message));
    }

    public function close_gate_person(Request $request)
    {
        $response = 0;
        if (isset($request->device_id)) {
            $location_device = \App\LocationDevices::find($request->device_id);
            if ($location_device->barrier_status < 1) {
                Artisan::call('command:CloseTicketReader', [
                    'device' => $request->device_id
                ]);
            } else {
                Artisan::call('command:UnlockBarrier', [
                    'device' => $request->device_id
                ]);
            }
            $location_device->user_id = Auth::id();
            $location_device->barrier_status = 0;
            $location_device->save();
            //            Artisan::call('command:CloseTicketReader', [
            //                'device' => $request->device_id
            //            ]);
            //            sleep(3);
            //            Artisan::call('command:CloseTicketReader', [
            //                'device' => $request->device_id
            //            ]);
            $response = 1;
        }

        $message = 'Please wait process is in progress';
        if (\Illuminate\Support\Facades\Session::has('open_gate_message')) {
            $message = \Illuminate\Support\Facades\Session::get('open_gate_message');
            \Illuminate\Support\Facades\Session::forget('open_gate_message');
        }
        if (\Illuminate\Support\Facades\Session::has('open_gate_status')) {
            $response = \Illuminate\Support\Facades\Session::get('open_gate_status');
            \Illuminate\Support\Facades\Session::forget('open_gate_status');
        }
        return json_encode(array('status' => $response, 'message' => $message));
    }

    /**
     * Get Transaction Details
     * @param Request $request
     * @return type
     */
    public function transaction_details(Request $request)
    {
        $search_type = '';
        $search_val = '';
        $response = array();
        $attendant_transaction = \App\TransactionView::sortable();
        if ((isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))) {
            if (isset($request->search_btn)) {
                $search_type = $request->search_type;
                $search_val = $request->search_val;
                if (!empty($request->search_type)) {
                    if ($request->search_type != 'open_gate_manual' && $request->search_type != 'open_gate_always') {
                        if ($request->search_type == 'first_name') {
                            $attendant_transaction = $attendant_transaction->where('first_name', 'LIKE', "%{$request->search_val}%");
                        } elseif ($request->search_type == 'vehicle') {
                            $attendant_transaction = $attendant_transaction->where('vehicle_num', 'LIKE', "%{$request->search_val}%");
                        }
                    }
                    if ($request->search_type != 'first_name' && $request->search_type != 'vehicle') {
                        $attendant_transaction = $attendant_transaction->whereHas('open_gate_manual_transaction', function ($query) use ($request) {
                            if ($request->search_type == 'open_gate_manual') {
                                $query->where('type', 'MO');
                            } elseif ($request->search_type == 'open_gate_always') {
                                $query->where('type', 'AA');
                            }
                            if (!empty($request->search_val)) {
                                $query->whereHas('users', function ($q) use ($request) {
                                    $q->where('name', 'LIKE', "%{$request->search_val}%");
                                })->with([
                                    'users' =>
                                    function ($q) use ($request) {
                                        $q->where('name', 'LIKE', "%{$request->search_val}%");
                                    }
                                ]);
                            }
                        })->with([
                            'open_gate_manual_transaction' =>
                            function ($query) use ($request) {
                                if ($request->search_type == 'open_gate_manual') {
                                    $query->where('type', 'MO');
                                } elseif ($request->search_type == 'open_gate_always') {
                                    $query->where('type', 'AA');
                                }
                                if (!empty($request->search_val)) {
                                    $query->whereHas('users', function ($q) use ($request) {
                                        $q->where('name', 'LIKE', "%{$request->search_val}%");
                                    })->with([
                                        'users' =>
                                        function ($q) use ($request) {
                                            $q->where('name', 'LIKE', "%{$request->search_val}%");
                                        }
                                    ]);
                                }
                            }
                        ]);
                    }
                }
            }
        }
        $type = 'Person Ticket';
        $attendant_transaction = $attendant_transaction
            ->whereRaw("BINARY type <> '$type'")
            ->orderBy('check_in', 'desc')
            ->paginate(25);
        if ($attendant_transaction->count() > 0) {
            foreach ($attendant_transaction as $transaction) {
                $data = array();
                $data['operator_name_entrance'] = 'N/A';
                $data['operator_name_exit'] = 'N/A';
                $data['image'] = url('/plugins/images/logo_2.png');
                $image = \App\TransactionImages::where('transaction_id', $transaction->attendant_transaction_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($image) {
                    if ($image->image_path != null) {
                        $data['image'] = $image->image_path;
                    }
                }
                $data['entry_device'] = '--';
                $entry_transaction = \App\TransactionImages::where([
                    ['transaction_id', $transaction->attendant_transaction_id],
                    ['type', 'in']
                ])
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($entry_transaction) {
                    if ($entry_transaction->image_path != null) {
                        $data['image'] = $entry_transaction->image_path;
                    }
                    $entry_device = \App\LocationDevices::find($entry_transaction->device_id);
                    if ($entry_device) {
                        if (!$entry_device->has_gate) {
                            $device_ticket_readers = \App\DeviceTicketReaders::where('device_id', $entry_device->id)
                                ->first();
                            if ($device_ticket_readers) {
                                $entry_device = \App\LocationDevices::find($device_ticket_readers->ticket_reader_id);
                            }
                        }
                        $data['entry_device'] = $entry_device->device_name;
                        $openGateManualTransaction = $transaction->open_gate_manual_transaction()
                            ->where('location_device_id', $entry_device->id)
                            ->first();
                        if ($openGateManualTransaction) {
                            $operator = $openGateManualTransaction->users;
                            if ($operator) {
                                $data['operator_name_entrance'] = $operator->name ?: 'N/A';
                            }
                        }
                    }
                }
                $data['exit_device'] = '--';
                $exit_transaction = \App\TransactionImages::where([
                    ['transaction_id', $transaction->attendant_transaction_id],
                    ['type', 'out']
                ])
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($exit_transaction) {
                    if ($exit_transaction->image_path != null) {
                        $data['image'] = $exit_transaction->image_path;
                    }
                    $exit_device = \App\LocationDevices::find($exit_transaction->device_id);
                    if ($exit_device) {
                        if (!$exit_device->has_gate) {
                            $device_ticket_readers = \App\DeviceTicketReaders::where('device_id', $exit_device->id)
                                ->first();
                            if ($device_ticket_readers) {
                                $exit_device = \App\LocationDevices::find($device_ticket_readers->ticket_reader_id);
                            }
                        }
                        $data['exit_device'] = $exit_device->device_name;
                        $openGateManualTransaction = $transaction->open_gate_manual_transaction()
                            ->where('location_device_id', $exit_device->id)
                            ->first();
                        if ($openGateManualTransaction) {
                            $operator = $openGateManualTransaction->users;
                            if ($operator) {
                                $data['operator_name_exit'] = $operator->name ?: 'N/A';
                            }
                        }
                    }
                }
                $data['check_in'] = date('d/m/Y H:i', strtotime($transaction->check_in));
                $data['check_out'] = $transaction->check_out == null ? '--' : date('d/m/Y H:i', strtotime($transaction->check_out));
                if ($transaction->vehicle_num != null) {
                    $data['vehicle'] = $transaction->vehicle_num;
                } else {
                    $data['vehicle'] = 'Paid Vehicle';
                }
                if ($transaction->first_name === '0') {
                    $transaction->first_name = 'Paid Vehicle';
                }
                $data['name'] = $transaction->first_name == 'Paid Vehicle' ? __('dashboard.paid_vehicle') : $transaction->first_name . ' ' . $transaction->last_name;
                $data['email'] = !empty($transaction->email) ? $transaction->email : 'N/A';

                $data['phone_number'] = $transaction->phone_number == null ? 'N/A' : $transaction->phone_number;
                $data['id'] = $transaction->booking_id;
                $booking_payment_details = \App\BookingPayments::where('booking_id', $transaction->booking_id)->first();
                $is_online = 0;
                if ($booking_payment_details) {
                    $is_online = $booking_payment_details->is_online;
                }
                $data['is_online'] = $is_online;
                $data['type'] = $transaction->type;
                $vehiclePaymentTransactions = \App\TransactionPaymentVehicles::with('location_devices')
                    ->where('attendant_transaction_id', $transaction->attendant_transaction_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                $data['vehicle_payment_transactions'] = $vehiclePaymentTransactions;
                $response[] = (object) $data;
            }
        }
        return view('transactions.transaction_details', [
            'get_last_5_transactions' => $response,
            'transactions' => $attendant_transaction,
            'search_type' => $search_type,
            'search_val' => $search_val,
        ]);
    }

    /**
     * Get Transaction Details
     * @param Request $request
     * @return type
     */
    public function transaction_details_p(Request $request)
    {
        $search_type = '';
        $search_val = '';
        $response = array();
        $attendant_transaction = \App\TransactionView::sortable();
        if ((isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))) {
            if (isset($request->search_btn)) {
                $search_type = $request->search_type;
                $search_val = $request->search_val;
                if (!empty($request->search_type)) {
                    if ($request->search_type == 'first_name') {
                        $attendant_transaction = $attendant_transaction->where('first_name', 'LIKE', "%{$request->search_val}%");
                    } elseif ($request->search_type == 'vehicle') {
                        $attendant_transaction = $attendant_transaction->where('vehicle_num', 'LIKE', "%{$request->search_val}%");
                    } else {
                        $attendant_transaction = $attendant_transaction->where('first_name', 'LIKE', "%{$request->search_val}%");
                        $attendant_transaction = $attendant_transaction->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                    }
                } else {
                    $attendant_transaction = $attendant_transaction->where('first_name', 'LIKE', "%{$request->search_val}%");
                    $attendant_transaction = $attendant_transaction->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                }
            }
        }
        $type = 'Person Ticket';
        $attendant_transaction = $attendant_transaction
            ->whereRaw("BINARY type = '$type'")
            ->orderBy('check_in', 'desc')
            ->paginate(25);

        if ($attendant_transaction->count() > 0) {
            foreach ($attendant_transaction as $transaction) {
                $data = array();
                $data['image'] = url('/plugins/images/logo_2.png');
                $image = \App\TransactionImages::where('transaction_id', $transaction->attendant_transaction_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($image) {
                    if ($image->image_path != null) {
                        $data['image'] = $image->image_path;
                    }
                }
                $data['entry_device'] = '--';
                $entry_transaction = \App\TransactionImages::where([
                    ['transaction_id', $transaction->attendant_transaction_id],
                    ['type', 'in']
                ])
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($entry_transaction) {
                    if ($entry_transaction->image_path != null) {
                        $data['image'] = $entry_transaction->image_path;
                    }
                    $entry_device = \App\LocationDevices::find($entry_transaction->device_id);
                    if ($entry_device) {
                        $data['entry_device'] = $entry_device->device_name;
                    }
                }
                $data['exit_device'] = '--';
                $exit_transaction = \App\TransactionImages::where([
                    ['transaction_id', $transaction->attendant_transaction_id],
                    ['type', 'out']
                ])
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($exit_transaction) {
                    if ($exit_transaction->image_path != null) {
                        $data['image'] = $exit_transaction->image_path;
                    }
                    $exit_device = \App\LocationDevices::find($exit_transaction->device_id);
                    if ($exit_device) {
                        $data['exit_device'] = $exit_device->device_name;
                    }
                }

                $data['check_in'] = date('d/m/Y H:i', strtotime($transaction->check_in));
                $data['check_out'] = $transaction->check_out == null ? '--' : date('d/m/Y H:i', strtotime($transaction->check_out));
                if ($transaction->vehicle_num != null) {
                    $data['vehicle'] = $transaction->vehicle_num;
                } else {
                    $data['vehicle'] = 'Paid Vehicle';
                }
                $data['name'] = $transaction->first_name == 'Paid Vehicle' ? __('dashboard.paid_vehicle') : $transaction->first_name . ' ' . $transaction->last_name;
                $data['email'] = !empty($transaction->email) ? $transaction->email : 'N/A';

                $data['phone_number'] = $transaction->phone_number == null ? 'N/A' : $transaction->phone_number;
                $data['id'] = $transaction->booking_id;
                $booking_payment_details = \App\BookingPayments::where('booking_id', $transaction->booking_id)->first();
                $is_online = 0;
                if ($booking_payment_details) {
                    $is_online = $booking_payment_details->is_online;
                }
                $data['is_online'] = $is_online;
                $data['type'] = $transaction->type;
                $vehiclePaymentTransactions = \App\TransactionPaymentVehicles::with('location_devices')
                    ->where('attendant_transaction_id', $transaction->attendant_transaction_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                $data['vehicle_payment_transactions'] = $vehiclePaymentTransactions;
                $response[] = (object) $data;
            }
        }
        return view('transactions.transaction_details_p', [
            'get_last_5_transactions' => $response,
            'transactions' => $attendant_transaction,
            'search_type' => $search_type,
            'search_val' => $search_val,
        ]);
    }

    public function device_transaction(Request $request, $type, $id)
    {
        $transactions = array();
        $device_details = \App\LocationDevices::find($id);
        if (!$device_details) {
            return redirect('/dashboard');
        }
        if ($type == 'tr') {
            $transactions = $this->get_device_transactions($device_details, null, null);
        } elseif ($type == 'ptr') {
            $transactions = $this->get_device_transactions($device_details, null, null);
        } elseif ($type == 'pr') {
            $transactions = $this->get_device_transactions($device_details, null, null);
        } elseif ($type == 'ptv') {
            $transactions = $this->get_vehicle_payment_transactions($device_details, null);
        } elseif ($type == 'ptp') {
            $transactions = $this->get_person_payment_transactions($device_details, null);
        } else {
            return redirect('/dashboard');
        }
        return view('transactions.device_transactions', [
            'device_details' => $device_details,
            'transactions' => $transactions,
            'type' => $type,
        ]);
    }

    public function change_gate_barrier_status(Request $request)
    {
        $response = 0;
        $message = 'Please wait process is in progress';
        if (isset($request->device_id)) {
            if ($request->status == 1) {
                $data = $this->locked_open_gate($request->device_id);
                if ($data->status) {
                    $response = 1;
                    $message = $data->message;
                }
            } elseif ($request->status == 2) {
                $data = $this->locked_close_gate($request->device_id);
                if ($data->status) {
                    $response = 1;
                    $message = $data->message;
                }
            } elseif ($request->status == 3) {
                $device = \App\LocationDevices::find($request->device_id);
                if ($device) {
                    if ($device->barrier_status != 0) {
                        Artisan::call('command:UnlockBarrier', [
                            'device' => $request->device_id
                        ]);
                    }
                    $device->user_id = Auth::id();
                    $device->barrier_status = 3;
                    $device->save();
                    $response = 1;
                    $message = 'Always Access Open';
                }
            } else {
                $data = $this->un_lock_gate($request->device_id);
                if ($data->status) {
                    $response = 1;
                    $message = $data->message;
                }
            }
        }
        return json_encode(array('status' => $response, 'message' => $message));
    }

    public function un_lock_gate($device_id)
    {
        $response = 0;
        if (isset($device_id)) {
            $device = \App\LocationDevices::find($device_id);
            if ($device && $device->barrier_status != 0) {
                Artisan::call('command:UnlockBarrier', [
                    'device' => $device_id
                ]);
            }
            $device->user_id = Auth::id();
            $device->barrier_status = 0;
            $device->save();
            $response = 1;
        }

        $message = 'Please wait process is in progress';
        if (\Illuminate\Support\Facades\Session::has('open_gate_message')) {
            $message = \Illuminate\Support\Facades\Session::get('open_gate_message');
            \Illuminate\Support\Facades\Session::forget('open_gate_message');
        }
        if (\Illuminate\Support\Facades\Session::has('open_gate_status')) {
            $response = \Illuminate\Support\Facades\Session::get('open_gate_status');
            \Illuminate\Support\Facades\Session::forget('open_gate_status');
        }
        return (object) array('status' => $response, 'message' => $message);
    }

    public function locked_open_gate($device_id)
    {
        $response = 0;
        if (isset($device_id)) {
            $device = \App\LocationDevices::find($device_id);
            if ($device) {
                Artisan::call('command:LockedOpenTicketReader', [
                    'device' => $device_id
                ]);
            }
            $device->user_id = Auth::id();
            $device->barrier_status = 1;
            $device->save();
            $response = 1;
        }
        $message = 'Please wait process is in progress';
        if (\Illuminate\Support\Facades\Session::has('open_gate_message')) {
            $message = \Illuminate\Support\Facades\Session::get('open_gate_message');
            \Illuminate\Support\Facades\Session::forget('open_gate_message');
        }
        if (\Illuminate\Support\Facades\Session::has('open_gate_status')) {
            $response = \Illuminate\Support\Facades\Session::get('open_gate_status');
            \Illuminate\Support\Facades\Session::forget('open_gate_status');
        }
        return (object) array('status' => $response, 'message' => $message);
    }

    public function locked_close_gate($device_id)
    {
        $response = 0;
        if (isset($device_id)) {
            $device = \App\LocationDevices::find($device_id);
            if ($device) {
                Artisan::call('command:LockedCloseTicketReader', [
                    'device' => $device_id
                ]);
            }
            $device->user_id = Auth::id();
            $device->barrier_status = 2;
            $device->save();
            $response = 1;
        }

        $message = 'Please wait process is in progress';
        if (\Illuminate\Support\Facades\Session::has('open_gate_message')) {
            $message = \Illuminate\Support\Facades\Session::get('open_gate_message');
            \Illuminate\Support\Facades\Session::forget('open_gate_message');
        }
        if (\Illuminate\Support\Facades\Session::has('open_gate_status')) {
            $response = \Illuminate\Support\Facades\Session::get('open_gate_status');
            \Illuminate\Support\Facades\Session::forget('open_gate_status');
        }
        return (object) array('status' => $response, 'message' => $message);
    }

    public function open_payment_terminal(Request $request)
    {
        $response = 0;
        $device_id = $request->device_id;
        $switch_id = $request->switch_id;
        $relay = $request->relay;
        $responseData = '';
        $status = 1;
        if (isset($device_id, $switch_id)) {
            $device = LocationDevices::find($device_id);
            $switch = LocationDevices::with('ports')->find($switch_id);
            $get_relay = DevicePort::where(['id' => $relay, 'device_id' => $switch->id])->first();
            $relay_number = $get_relay->relay_number;
            $result = 0; //$this->getStatus($switch, $relay_number);
            if ($result == 1) {
                $message = __('devices.already_open');
            } elseif ($result == 0) {
                $res = $this->sendCommand($device, $switch, $status, $relay_number);
                $message = $res;
            } else {
                $message = __('devices.relay_error');
            }
        }
        $device->user_id = Auth::id();
        $device->save();
        $response = 1;
        // $message = 'Please wait process is in progress';
        if (\Illuminate\Support\Facades\Session::has('open_payment_terminal')) {
            $message = \Illuminate\Support\Facades\Session::get('open_payment_terminal');
            \Illuminate\Support\Facades\Session::forget('open_payment_terminal');
        }
        return array('status' => $response, 'message' => $message);
    }

    public function close_payment_terminal(Request $request)
    {
        $response = 0;
        $device_id = $request->device_id;
        $switch_id = $request->switch_id;
        $relay = $request->relay;
        $responseData = '';
        $status = 1;
        if (isset($device_id, $switch_id)) {
            $device = LocationDevices::find($device_id);
            $switch = LocationDevices::with('ports')->find($switch_id);
            $get_relay = DevicePort::where(['id' => $relay, 'device_id' => $switch->id])->first();
            $relay_number = $get_relay->relay_number;
            $result = 0; //$this->getStatus($switch, $relay_number);
            if ($result == 1) {
                $message = __('devices.already_close');
            } elseif ($result == 0) {
                $res = $this->sendCommand($device, $switch, $status, $relay_number);
                $message = $res;
            } else {
                $message = __('devices.relay_error');
            }
        }
        $device->user_id = Auth::id();
        $device->save();
        $response = 1;
        if (\Illuminate\Support\Facades\Session::has('close_payment_terminal')) {
            $message = \Illuminate\Support\Facades\Session::get('close_payment_terminal');
            \Illuminate\Support\Facades\Session::forget('close_payment_terminal');
        }
        return array('status' => $response, 'message' => $message);
    }

    public function getStatus($switch, $relay_number)
    {
        try {
            $ip_address = $switch->device_ip;
            $url = "http://" . $ip_address . "/relay_cgi_load.cgi";
            $http = new Client();
            $data = $http->get($url);
            // $str="&0&8&1&1&0&1&1&1&1&1&";
            $res = substr($data->getBody(), 1, -1);
            $status = '';
            $result = explode("&", $res);
            $relay_count = $result[1];
            $relay_number += 2;
            if ($result[0] == 0) {
                for ($i = 2; $i < $relay_count; $i++) {
                    switch ($relay_number) {
                        case $i:
                            $status = $result[$i];
                            break;
                    }
                }
            } else {
                $status = 00;
            }
            return $status;
        } catch (Exception $ex) {
            response()->json([
                'status' => "error",
                'message' => $ex->getMessage() . ' ' . $ex->getLine()
            ]);
        }
    }

    public function sendCommand($device, $switch, $status, $relay)
    {
        try {
            $ip_address = $switch->device_ip;
            $type = 1;
            $password = $device->password;
            $time = $switch->barrier_close_time;
            $timer = $time / 1000;
            $jogging_timer = $time / 100;
            $message = '';
            $http = new Client();
            if ($status == 1) {
                $url = 'http://' . $ip_address . '/relay_cgi.cgi?type=' . $type . '&relay=' . $relay . '&on=' . $status . '&time=' . $jogging_timer . '&pwd=' . $password;
                // $str="&0&0&0&1&0&";
                $data = $http->get($url);
                $res = substr($data->getBody(), 1, -1);

                //sleep($timer);
                //$status = 0;
                //$url = 'http://' . $ip_address . '/relay_cgi.cgi?type=' . $type . '&relay=' . $relay . '&on=' . $status . '&time='.$jogging_timer.'&pwd=' . $password;
                //$data = $http->get($url);
                //$res = substr($data->getBody(), 1, -1);
                $result = explode('&', $res);
                if ($result[0] != 0) {
                    $message = __('devices.open_error');
                } else {
                    $message = __('devices.open_door');
                }
            } else if ($status == 0) {
                $url = 'http://' . $ip_address . '/relay_cgi.cgi?type=' . $type . '&relay=' . $relay . '&on=' . $status . '&time=' . $jogging_timer . '&pwd=' . $password;
                // $str="&0&0&0&0&0&";
                $data = $http->get($url);
                //sleep($timer);
                //$status = 1;
                //$url = 'http://' . $ip_address . '/relay_cgi.cgi?type=' . $type . '&relay=' . $relay . '&on=' . $status . '&time='.$jogging_timer.'&pwd=' . $password;
                $data = $http->get($url);
                $res = substr($data->getBody(), 1, -1);
                $result = explode('&', $res);
                if ($result[0] != 0) {
                    $message = __('devices.close_error');
                } else {
                    $message = __('devices.close_door');
                }
            }
            return $message;
        } catch (Exception $ex) {
            response()->json([
                'status' => "error",
                'message' => $ex->getMessage() . ' ' . $ex->getLine()
            ]);
        }
    }

    //    public function update_open_manual_transactions() {
    //        $attendant_transaction = \App\TransactionView::get();
    //        $count = 0;
    //        foreach ($attendant_transaction as $transaction) {
    //            $entry_transaction = \App\TransactionImages::where([
    //                        ['transaction_id', $transaction->attendant_transaction_id],
    //                        ['type', 'in']
    //                    ])
    //                    ->orderBy('created_at', 'desc')
    //                    ->first();
    //            if ($entry_transaction) {
    //                $entry_device = \App\LocationDevices::find($entry_transaction->device_id);
    //                if ($entry_device) {
    //                    if (!$entry_device->has_gate) {
    //                        $device_ticket_readers = \App\DeviceTicketReaders::where('device_id', $entry_device->id)
    //                                ->first();
    //                        if ($device_ticket_readers) {
    //                            $entry_device = \App\LocationDevices::find($device_ticket_readers->ticket_reader_id);
    //                        }
    //                    }
    //                }
    //                $openGateManualTransaction = $transaction->open_gate_manual_transaction()
    //                        ->orderBy('created_at', 'asc')
    //                        ->first();
    //                if ($openGateManualTransaction) {
    //                    $openGateManualTransaction->location_device_id = $entry_device->id;
    //                    $openGateManualTransaction->save();
    //                }
    //            }
    //            $exit_transaction = \App\TransactionImages::where([
    //                        ['transaction_id', $transaction->attendant_transaction_id],
    //                        ['type', 'out']
    //                    ])
    //                    ->orderBy('created_at', 'desc')
    //                    ->first();
    //            if ($exit_transaction) {
    //                $exit_device = \App\LocationDevices::find($exit_transaction->device_id);
    //                if ($exit_device) {
    //                    if (!$exit_device->has_gate) {
    //                        $device_ticket_readers = \App\DeviceTicketReaders::where('device_id', $exit_device->id)
    //                                ->first();
    //                        if ($device_ticket_readers) {
    //                            $exit_device = \App\LocationDevices::find($device_ticket_readers->ticket_reader_id);
    //                        }
    //                    }
    //                }
    //                $openGateManualTransaction = $transaction->open_gate_manual_transaction()
    //                        ->orderBy('created_at', 'desc')
    //                        ->first();
    //                if ($openGateManualTransaction) {
    //                    $openGateManualTransaction->location_device_id = $exit_device->id;
    //                    $openGateManualTransaction->save();
    //                }
    //            }
    //            $count = $count + 1;
    //        }
    //        echo $count;
    //        exit;
    //    }
}
