<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use App\Language;
use App\LocationOptions;
use App\LocationImages;
use App\LocationTimings;
use App\UserlistUsers;
use App\WhitelistUsers;
use App\User;
use App\Customer;
use App\Profile;
use App\CustomerVehicleInfo;
use App\Bookings;
use App\BookingPayments;
use App\Products;
use App\Attendants;
use App\AttendantTransactions;
use App\TommyReservationParents;
use App\TommyReservationChildrens;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DaySubscriptionsController extends Controller
{

    public $controller = 'App\Http\Controllers\DaySubscriptionsController';
    public $qr_code_data_type;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->qr_code_data_type = 'csv';
    }

    /**
     * Get All Booking Payments
     * @return type
     */
    public function person_subscriptions(Request $request) {
        $search_type = '';
        $search_val = '';
        $bookingPayments = \App\BookingPaymentsView::sortable();
        $bookingPayments = $bookingPayments->select(
                'email', 
                'amount', 
                'check_in', 
                'check_out', 
                'dob', 
                'booking_id');
        $totalAmount = \App\BookingPaymentsView::sortable();
        if ((isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))) {
            if (isset($request->search_btn)) {
                $search_type = $request->search_type;
                $search_val = $request->search_val;
                if (!empty($request->search_type)) {

                    if ($request->search_type == 'first_name') {
                        $bookingPayments = $bookingPayments->where('first_name', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->where('first_name', 'LIKE', "%{$request->search_val}%");
                    } elseif ($request->search_type == 'vehicle') {
                        $bookingPayments = $bookingPayments->where('vehicle_num', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->where('vehicle_num', 'LIKE', "%{$request->search_val}%");
                    } elseif ($request->search_type == 'email') {
                        $bookingPayments = $bookingPayments->where('email', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->where('email', 'LIKE', "%{$request->search_val}%");
                    } else {
                        $bookingPayments = $bookingPayments->where('first_name', 'LIKE', "%{$request->search_val}%");
                        $bookingPayments = $bookingPayments->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                        $bookingPayments = $bookingPayments->orWhere('email', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->where('first_name', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->orWhere('email', 'LIKE', "%{$request->search_val}%");
                    }
                } else {
                    $bookingPayments = $bookingPayments->where('first_name', 'LIKE', "%{$request->search_val}%");
                    $bookingPayments = $bookingPayments->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                    $bookingPayments = $bookingPayments->orWhere('email', 'LIKE', "%{$request->search_val}%");
                    $totalAmount = $totalAmount->where('first_name', 'LIKE', "%{$request->search_val}%");
                    $totalAmount = $totalAmount->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                    $totalAmount = $totalAmount->orWhere('email', 'LIKE', "%{$request->search_val}%");
                }
            }
        }
        $bookingPayments = $bookingPayments->where('type', 6);
        $bookingPayments = $bookingPayments->whereNotNull('email');
        $bookingPayments = $bookingPayments->where('check_in', '>=', date('Y-m-d 00:00:00'));
        $bookingPayments = $bookingPayments->where('check_out', '>=', date('Y-m-d 23:59:59'));
        $bookingPayments = $bookingPayments->where('check_out', '!=', date('Y-12-31 23:59:59'));
//        $bookingPayments = $bookingPayments->groupBy('email');
        $bookingPayments = $bookingPayments->paginate(25);
        $totalAmount = \App\BookingPaymentsView::where('type', 6)
                ->whereNotNull('email')
                ->where('check_in', '>=', date('Y-m-d 00:00:00'))
                ->where('check_out', '>=', date('Y-m-d 23:59:59'))
                ->sum('amount');
        return view('day.person_payments', compact('bookingPayments', 'totalAmount', 'search_type', 'search_val'));
    }

    public function parking_subscriptions(Request $request) {
        $search_type = '';
        $search_val = '';
        $bookingPayments = \App\BookingPaymentsView::sortable();
        $bookingPayments = $bookingPayments->select(
                'email', 
                'amount', 
                'check_in', 
                'check_out', 
                'vehicle_num', 
                'is_online', 
                'booking_id');
        $totalAmount = \App\BookingPaymentsView::sortable();
        if ((isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))) {
            if (isset($request->search_btn)) {
                $search_type = $request->search_type;
                $search_val = $request->search_val;
                if (!empty($request->search_type)) {

                    if ($request->search_type == 'first_name') {
                        $bookingPayments = $bookingPayments->where('first_name', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->where('first_name', 'LIKE', "%{$request->search_val}%");
                    } elseif ($request->search_type == 'vehicle') {
                        $bookingPayments = $bookingPayments->where('vehicle_num', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->where('vehicle_num', 'LIKE', "%{$request->search_val}%");
                    } elseif ($request->search_type == 'email') {
                        $bookingPayments = $bookingPayments->where('email', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->where('email', 'LIKE', "%{$request->search_val}%");
                    } else {
                        $bookingPayments = $bookingPayments->where('first_name', 'LIKE', "%{$request->search_val}%");
                        $bookingPayments = $bookingPayments->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                        $bookingPayments = $bookingPayments->orWhere('email', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->where('first_name', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                        $totalAmount = $totalAmount->orWhere('email', 'LIKE', "%{$request->search_val}%");
                    }
                } else {
                    $bookingPayments = $bookingPayments->where('first_name', 'LIKE', "%{$request->search_val}%");
                    $bookingPayments = $bookingPayments->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                    $bookingPayments = $bookingPayments->orWhere('email', 'LIKE', "%{$request->search_val}%");
                    $totalAmount = $totalAmount->where('first_name', 'LIKE', "%{$request->search_val}%");
                    $totalAmount = $totalAmount->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                    $totalAmount = $totalAmount->orWhere('email', 'LIKE', "%{$request->search_val}%");
                }
            }
        }
        // $bookingPayments = $bookingPayments->where('type', 4);
        // $bookingPayments = $bookingPayments->whereNotNull('email');
        // $bookingPayments = $bookingPayments->where('check_in', '>=', date('Y-m-d 00:00:00'));
        // $bookingPayments = $bookingPayments->where('check_out', '>=', date('Y-m-d 23:59:59'));
        // $bookingPayments = $bookingPayments->where('check_out', '!=', date('Y-12-31 23:59:59'));
//        $bookingPayments = $bookingPayments->groupBy('email');
        $bookingPayments = $bookingPayments->paginate(25);
        $totalAmount = \App\BookingPaymentsView::where('type', 4)
                ->whereNotNull('email')
                ->where('check_in', '>=', date('Y-m-d 00:00:00'))
                ->where('check_out', '>=', date('Y-m-d 23:59:59'))
                ->sum('amount');
        return view('day.parking_payments', compact('bookingPayments', 'totalAmount', 'search_type', 'search_val'));
    }

    public function download($id) {
        try {
            $current_locale = \App::getLocale();
            $booking = Bookings::find($id);
            if (!$booking || $booking->live_id == 0) {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('payments.booking_not_found'));
                Session::flash('icon', 'error');
                return redirect()->back();
            }
            $barcode_number = str_pad($booking->live_id, 9, '0', STR_PAD_LEFT);
            $location_details = LocationOptions::first();
            if (!$location_details) {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('payments.location_not_found'));
                Session::flash('icon', 'error');
                return redirect()->back();
            }
            if (date('Y-m-d H:i:s', strtotime($booking->checkin_time)) == date('Y-m-d 00:00:00') && date('Y-m-d H:i:s', strtotime($booking->checkout_time)) == date('Y-m-d 23:59:59')) {
                if ($booking->type == 6 || $booking->type == 10) {
                    if ($current_locale == 'en') {
                        $title = 'Day ticket person';
                    } else {
                        $title = 'Dagkaart personen';
                    }
                    $ticket_type = 'person_ticket';
                    $ticket_type_name = 'person_ticket';
                    $pdf = $this->generate_booking_pdf($booking->id, $title, $ticket_type, $ticket_type_name, $barcode_number);
                } else {
                    if ($current_locale == 'en') {
                        $title = 'Day ticket parking';
                    } else {
                        $title = 'Dagkaart parkeren';
                    }
                    $ticket_type = 'day_ticket';
                    $ticket_type_name = 'vehicle_ticket';
                    $pdf = $this->generate_booking_pdf($booking->id, $title, $ticket_type, $ticket_type_name, $barcode_number);
                }
                return $pdf;
            }
            else if (date('Y-m-d H:i:s', strtotime($booking->checkin_time)) > date('Y-m-d 23:59:59') && date('Y-m-d H:i:s', strtotime($booking->checkout_time)) > date('Y-m-d 23:59:59')) {
                if ($booking->type == 6 || $booking->type == 10) {
                    if ($current_locale == 'en') {
                        $title = 'Day ticket person';
                    } else {
                        $title = 'Dagkaart personen';
                    }
                    $ticket_type = 'person_ticket';
                    $ticket_type_name = 'person_ticket';
                    $pdf = $this->generate_booking_pdf($booking->id, $title, $ticket_type, $ticket_type_name, $barcode_number);
                } else {
                    if ($current_locale == 'en') {
                        $title = 'Day ticket parking';
                    } else {
                        $title = 'Dagkaart parkeren';
                    }
                    $ticket_type = 'day_ticket';
                    $ticket_type_name = 'vehicle_ticket';
                    $pdf = $this->generate_booking_pdf($booking->id, $title, $ticket_type, $ticket_type_name, $barcode_number);
                }
                return $pdf;
            }
            else {
                Session::flash('heading', 'Error!');
                Session::flash('message', 'This is not daily booking.');
                Session::flash('icon', 'error');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back();
        }
    }

    public function delete($id) {
        try {
            $current_locale = \App::getLocale();
            $booking = Bookings::find($id);
            if ($booking->type==6) {
				$booking->delete();
                Session::flash('heading', 'Success!');
				Session::flash('message', __('payments.ticket_delete'));
				Session::flash('icon', 'success');
				return redirect('day/person');
            }
            if ($booking->type==4) {
				$booking->delete();
                Session::flash('heading', 'Success!');
				Session::flash('message', __('payments.ticket_delete'));
				Session::flash('icon', 'success');
				return redirect('day/parking');
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back();
        }
    }

    public function generate_booking_pdf($booking_id, $title, $ticket_type, $ticket_type_name, $barcode_number) {
        try {
            $current_locale = \App::getLocale();

            $booking_details = Bookings::find($booking_id);

            $booking_live_id = $booking_details->live_id;

            $location_options = LocationOptions::find(1);

            $location_id = $location_options->live_id;
            $postal_code = str_replace(' ', '', $location_options->postal_code);
            $key = $postal_code . '_' . $location_id;

            if ($booking_details->is_user_logged_in == 1) {
                $user_id = $booking_details->customer_id;
                $vehicle_id = $booking_details->customer_vehicle_info_id;
                $user_profile = Profile::where('customer_id', '=', $user_id)->first();
                $user_name = $user_profile->first_name . ' ' . $user_profile->last_name;
                $user_phone = $user_profile->phone_num;
                $vehicle_details = CustomerVehicleInfo::find($vehicle_id);
                $vehicle_number_plate = $vehicle_details->num_plate;
                $email = $user_profile->email;
            } else {
                $user_name = ($booking_details->first_name == 'Paid Vehicle' || $booking_details->first_name == 'Paid Person') ? __('dashboard.paid_vehicle') : $booking_details->first_name . ' ' . $booking_details->last_name;
                $user_phone = $booking_details->phone_number;
                $vehicle_number_plate = $booking_details->vehicle_num;
                $email = $booking_details->email;
            }
            if ($booking_details->type == 6 || $booking_details->type == 10) {
                $vehicle_number_plate = FALSE;
            }
            if ($booking_details->sender_name == '' || $booking_details->sender_name == null) {
                $user_info = Profile::where('user_id', $booking_details->customer_id)->first();
                if ($user_info) {
                    $sender = $user_info->first_name . ' ' . $user_info->last_name;
                } else {
                    $sender = 'N/A';
                }
            } else {
                $sender = $booking_details->sender_name;
            }
            if ($booking_details->message == '' || $booking_details->message == null) {
                $note = '';
            } else {
                $note = str_replace(",", ";", $booking_details->message);
            }
            $user_name_qr = $this->qr_code_encryption($user_name, $key);
            $user_phone_qr = $this->qr_code_encryption($user_name, $key);
            $num_plate_vehicle = $this->qr_code_encryption(preg_replace('/[^\w]/', '', $vehicle_number_plate), $key);
            $email_qr = $this->qr_code_encryption($email, $key);
            $location_language_id = $location_options->language_id;
            $language_details = Language::find($location_language_id);
            $language_code = $language_details->code;
            $location_title = $location_options->title;
            $bike_range = $location_options->bike_range_start . '-' . $location_options->bike_range_end;
            $door_range = $location_options->door_range_start . '-' . $location_options->door_range_end;
            $ev_charger_range = $location_options->ev_charger_range_start . '-' . $location_options->ev_charger_range_end;
            $ev_charger_energy = $location_options->ev_charger_energy;
            $location_address = $location_options->address;
            $location_contact = $location_options->owner_phone_num;
            $location_description = strip_tags($location_options->description);
            $amount = 0.00;
            $payment_details = BookingPayments::where('booking_id', '=', $booking_id)->first();
            if ($payment_details) {
                $amount = $payment_details->amount ? $payment_details->amount : 0.00;
            }
            $checkin_time = date('d/m/Y', strtotime($booking_details->checkin_time));
            $checkout_time = date('d/m/Y', strtotime($booking_details->checkout_time));
            $dob = $booking_details->tommy_children_dob ? date('d/m/Y', strtotime($booking_details->tommy_children_dob)) : FALSE;
            $latitude = $location_options->latitude;
            $longitude = $location_options->longitude;
            /*            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
              <ticket type=\"$ticket_type_name|$booking_live_id\" name=\"$user_name_qr\" phone=\"$user_phone_qr\" email=\"$email_qr\" plate=\"$num_plate_vehicle\" lat=\"$latitude\" long=\"$longitude\" amount=\"$amount\" in=\"$checkin_time\" out=\"$checkout_time\" bike=\"$bike_range\" door=\"$door_range\" ev=\"$ev_charger_range\" el=\"$ev_charger_energy\" lang=\"$language_code\">
              </ticket>";
              $xml_conversion = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
              $xml_conversion .= "<ticket>";
              $xml_conversion .= "<type>" . $ticket_type_name . "|" . $booking_live_id . "</type>";
              $xml_conversion .= "<name>" . $user_name_qr . "</name>";
              $xml_conversion .= "<phone>" . $user_phone_qr . "</phone>";
              $xml_conversion .= "<email>" . $email_qr . "</email>";
              $xml_conversion .= "<plate>" . $num_plate_vehicle . "</plate>";
              $xml_conversion .= "<lat>" . $latitude . "</lat>";
              $xml_conversion .= "<long>" . $longitude . "</long>";
              $xml_conversion .= "<amount>" . $amount . "</amount>";
              $xml_conversion .= "<in>" . $checkin_time . "</in>";
              $xml_conversion .= "<out>" . $checkout_time . "</out>";
              $xml_conversion .= "<bike>" . $bike_range . "</bike>";
              $xml_conversion .= "<door>" . $door_range . "</door>";
              $xml_conversion .= "<ev>" . $ev_charger_range . "</ev>";
              $xml_conversion .= "<el>" . $ev_charger_energy . "</el>";
              $xml_conversion .= "<lang>" . $language_code . "</lang>";
              $xml_conversion .= "</ticket>";
              $xml_string = simplexml_load_string($xml_conversion);
              $json_xml_string = json_encode($xml_string);
              $csv_xml_string = $this->xml2csvSingle($xml_string); */
            if (\Auth::check()) {
                $user_id = \Auth::id();
                $user_profile = Profile::where('user_id', '=', $user_id)->first();
                $sender_name = $user_profile->first_name . ' ' . $user_profile->last_name;
            } else {
                $sender_name = 'ParkingShop';
            }
            /*            if ($this->qr_code_data_type == 'xml') {
              $qr_content = "^" . str_replace("~", "", $xml) . "~"; //preg_replace("/\r?\n/", "", $xml);
              } else if ($this->qr_code_data_type == 'json') {
              $qr_content = str_replace("~", "", $json_xml_string) . "~"; //preg_replace("/\r?\n/", "", $json_xml_string);
              } else {
              $qr_content = str_replace("~", "", $csv_xml_string) . "~"; //preg_replace("/\r?\n/", "", $csv_xml_string);
              } */
            $qr_content = $barcode_number;
            $qr_code_200 = base64_encode(QrCode::format('png')->size(180)->generate($qr_content));
            if ($current_locale == 'en') {
                $pdf_title = 'Reserving ' . $title;
            } else {
                $pdf_title = 'Reservering ' . $title;
            }
            $type = 0;
            $how_to_use = 0;
            $parking_location_image_pdf = 'location_img.jpg';
            $products = Products::where('type', $ticket_type)->first();
            if ($products) {
                $current_locale = \App::getLocale();
                if ($current_locale == 'en') {
                    $how_to_use = $products->how_to_use_en;
                } else {
                    $how_to_use = $products->how_to_use_nl;
                }
            }
            $title_message = __('pdf.PARKING_RESERVATION');
            $fie_name = $title . rand() . '.pdf';
            return \PDF::loadView('day.pdf', compact(    
                    'current_locale', 
                    'how_to_use', 
                    'type', 
                    'title', 
                    'pdf_title', 
                    'title_message', 
                    'user_name', 
                    'location_title', 
                    'checkin_time', 
                    'checkout_time', 
                    'location_address', 
                    'amount', 
                    'vehicle_number_plate', 
                    'location_description', 
                    'location_contact', 
                    'parking_location_image_pdf', 
                    'qr_code_200', 
                    'latitude', 
                    'longitude', 
                    'dob'))->setWarnings(false)->download($fie_name);
        } 
        catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function resend($id) {
        try {
            $current_locale = 'nl';
            $booking = Bookings::find($id);
            if (!$booking || $booking->live_id == 0) {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('payments.booking_not_found'));
                Session::flash('icon', 'error');
                return redirect()->back();
            }
            if (date('Y-m-d H:i:s', strtotime($booking->checkin_time)) == date('Y-m-d 00:00:00') && date('Y-m-d H:i:s', strtotime($booking->checkout_time)) == date('Y-m-d 23:59:59')) {
                $ticket_type = 'day_ticket';
                $title = 'Vehicle Ticket';
                if ($booking->type == 6 || $booking->type == 10) {
                    $ticket_type = 'person_ticket';
                    $title = 'Person Ticket';
                }
                $data = array();
                $data['booking_id'] = $booking->live_id;
                $data['locale'] = $current_locale;
                $data['ticket_type'] = $ticket_type;
                $data['title'] = $title;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $user_id = auth()->user()->live_id;
                $Key = base64_encode($locationId . '_' . $user_id);
                $responseData['success'] = 0;
                try {
                    $http = new Client();

                    $response = $http->post(env('API_BASE_URL').'/api/send-day-ticket', [
                        'form_params' => [
                            'token' => $Key,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);

                    if ($responseData['success'] && isset($responseData['data'])) {
                        Session::flash('heading', 'Success!');
                        Session::flash('message', __('payments.ticket_sent'));
                        Session::flash('icon', 'success');
                        return redirect()->back();
                    }
                } 
                catch (\Exception $ex) {
                    
                }
            } 
            else if (date('Y-m-d H:i:s', strtotime($booking->checkin_time)) > date('Y-m-d 23:59:59') && date('Y-m-d H:i:s', strtotime($booking->checkout_time)) > date('Y-m-d 23:59:59')) {
                $ticket_type = 'day_ticket';
                $title = 'Vehicle Ticket';
                if ($booking->type == 6 || $booking->type == 10) {
                    $ticket_type = 'person_ticket';
                    $title = 'Person Ticket';
                }
                $data = array();
                $data['booking_id'] = $booking->live_id;
                $data['locale'] = $current_locale;
                $data['ticket_type'] = $ticket_type;
                $data['title'] = $title;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $user_id = auth()->user()->live_id;
                $Key = base64_encode($locationId . '_' . $user_id);
                $responseData['success'] = 0;
                try {
                    $http = new Client();

                    $response = $http->post(env('API_BASE_URL').'/api/send-day-ticket', [
                        'form_params' => [
                            'token' => $Key,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);

                    if ($responseData['success'] && isset($responseData['data'])) {
                        Session::flash('heading', 'Success!');
                        Session::flash('message', __('payments.ticket_sent'));
                        Session::flash('icon', 'success');
                        return redirect()->back();
                    }
                } 
                catch (\Exception $ex) {
                    
                }
            }
            else {
                Session::flash('heading', 'Error!');
                Session::flash('message', 'This is not daily booking.');
                Session::flash('icon', 'error');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back();
        }
    }

    public function qr_code_encryption($Text, $Key) {
        $password = substr(hash('sha256', $Key, true), 0, 32);
        $method = 'aes-256-cbc';
        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        $encrypted = base64_encode(openssl_encrypt($Text, $method, $password, OPENSSL_RAW_DATA, $iv)) . "|";
        return $encrypted;
    }

    public function xml2csv($xmlFile) {
        // Load the XML file
        $xml = $xmlFile;
        $csvData = '^';
        // Loop through the specified xpath
        foreach ($xml as $key => $item) {
            // Loop through the elements in this xpath
            if (count($item) > 0) {
                foreach ($item as $value) {
                    if (count($value) > 0) {
                        $csvData .= '"';
                        foreach ($value as $val) {
                            $csvData .= trim($val) . ',';
                        }
                        $csvData .= '"';
                    } else {
                        $csvData .= '"' . $value . '"' . ',';
                    }
                }
            } else {
                $csvData .= '"' . $item . '"';
            }
            // Trim off the extra comma
            $csvData = trim($csvData, ',');
            // Add an LF
            $csvData .= ">";
        }
        // Return the CSV data
        return $csvData;
    }

    public function xml2csvSingle($xmlFile) {
        // Load the XML file
        $xml = $xmlFile;
        $csvData = '^';
        // Loop through the elements in this xpath
        foreach ($xml as $key => $value) {
            $csvData .= '"' . trim($value) . '"' . ',';
        }
        // Return the CSV data
        return $csvData;
    }
    public function editPersonTicket($id){
        $booking=Bookings::with('booking_payments')->find($id);
        return response()->json($booking);
    }
    public function editParkingTicket($id){
        $booking=Bookings::with('booking_payments')->find($id);
        return response()->json($booking);
    }
    public function update(Request $request){
        $booking=Bookings::find($request->booking_id);
        if(!$request->email=='N/A'|| 'n/a'){
            $booking->email=$request->email;
        }
        $check_in = date('Y-m-d H:i:s', strtotime($request->arrival_time));
        $check_out = date('Y-m-d H:i:s', strtotime($request->departure_time));
        $booking->checkin_time=$check_in;
        $booking->checkout_time=$check_out;
        $booking->save();
        Session::flash('heading', 'Success!');
        Session::flash('message', __('booking.updated'));
        Session::flash('icon', 'success');
        return redirect()->back();
    }
}
