<?php

namespace App\Http\Controllers\PlateReaderController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Settings\Settings;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Process\Process as Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use GuzzleHttp\Client;

class VerifyVehicle extends Controller {

    public $confidence_val = 80;
    public $lang_id = FALSE;
    public $location_created_at = '1552661741';
    public $ticket_reader;
    public $lag_time = 30;
    public $settings;
    public $key = 'MTk3Nl8yODI=';
    public $url = "";
    public $location_setting = FALSE;

    public function __construct($key = NULL) {
        $this->url = env('API_BASE_URL');
        $this->ticket_reader = new \App\Http\Controllers\Settings\VerifyBookings();
        $this->settings = new \App\Http\Controllers\Settings\Settings();
        $location_setting = \App\LocationOptions::first();
        if ($key !== NULL) {
            $this->key = $key;
        } else {
            $user = \App\User::first();
            if ($user) {

                if ($location_setting) {
                    $key = $location_setting->live_id . '_' . $user->live_id;
                    $this->key = base64_encode($key);
                }
            }
        }
        $this->location_setting = $location_setting;
        if (!empty($location_setting->time_lag)) {
            $this->lag_time = $location_setting->time_lag;
        }
        $this->location_created_at = strtotime($location_setting->created_at);
    }

    public function check_is_booking_missed($vehicle_num, $booking = FALSE, $live_booking_id = FALSE, $live_booking_payment_id = FALSE) {
		try{
			$device_booking = \App\DeviceBookings::where([
						['vehicle_num', $vehicle_num]
					])
					->whereIn('device_id', [5, 25])
					->whereDate('created_at', \Carbon\Carbon::today())
					->orderBy('created_at', 'desc')
					->first();
			if (!$device_booking) {
				return FALSE;
			}
			if (empty($device_booking->device_id)) {
				return FALSE;
			}
			$device_id = $device_booking->device_id;
			$device_details = \App\LocationDevices::find($device_id);
			if (!$device_details) {
				return FALSE;
			}
			if ($device_details->device_direction != 'in') {
				return FALSE;
			}
			if (!$booking) {
				$booking = \App\Bookings::whereHas('attendant_transactions', function ($query) {
							$query->whereDate('check_in', \Carbon\Carbon::today());
						})
						->where('vehicle_num', $vehicle_num)
						->first();
				if ($booking) {
					return $booking;
				}
				$booking = \App\Bookings::where('vehicle_num', $vehicle_num)
						->where('checkin_time', '<=', date('Y-m-d H:i:s'))
						->where('checkout_time', '>', date('Y-m-d H:i:s'))
						->first();
				if (!$booking) {
					$dataArray = array(
						'first_name' => 'Paid Vehicle',
						'vehicle_num' => $vehicle_num,
						'type' => 4,
						'is_paid' => 0,
						'checkin_time' => date('Y-m-d H:i:s', strtotime($device_booking->created_at)),
						'amount' => 0,
						'payment_id' => 'Paid Vehicle',
						'attendant' => 1
					);

					$booking = new \App\Bookings();
					$booking->type = $dataArray['type'];
					$booking->first_name = $dataArray['first_name'];
					$booking->vehicle_num = $dataArray['vehicle_num'];
					$booking->checkin_time = $dataArray['checkin_time'];
					$booking->confidence = $device_booking->confidence;
					$booking->country_code = $device_booking->country_code;
					$booking->image_path = $device_booking->file_path;
					$booking->save();
					$bookingId = $booking->id;
					$booking_payment = new \App\BookingPayments();
					$booking_payment->booking_id = $bookingId;
					$booking_payment->amount = $dataArray['amount'];
					$booking_payment->payment_id = $dataArray['payment_id'];
					$booking_payment->checkin_time = $dataArray['checkin_time'];
					$booking_payment->save();
					$bookingPaymentId = $booking_payment->id;
					if (!$live_booking_id && !$live_booking_payment_id) {
						try {
							if (!$this->key) {
								$error_log = new \App\Http\Controllers\LogController();
								$error_log->log_create('import-key', 'custom: Import key not found');
								return FALSE;
							}
							$Key = $this->key;
							$http = new Client();
							$response = $http->post($this->url . '/api/store-booking-info', [
								'form_params' => [
									'token' => $Key,
									'data' => $dataArray
								],
							]);
							$responseData = json_decode((string) $response->getBody(), true);
							if (is_array($responseData) && array_key_exists('booking_info_live_id', $responseData['data'])) {
								$booking = \App\Bookings::find($bookingId);
								if ($booking) {
									$booking->live_id = $responseData['data']['booking_info_live_id'];
									$booking->save();
								}
							}
							if (is_array($responseData) && array_key_exists('booking_payment_live_id', $responseData['data'])) {
								$booking_payment = \App\BookingPayments::find($bookingPaymentId);
								if ($booking_payment) {
									$booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
									$booking_payment->save();
								}
							}
						} catch (\Exception $ex) {
							$error_log = new \App\Http\Controllers\LogController();
							$error_log->log_create('get_vehicle_booking', $ex->getMessage(), $ex->getTraceAsString());
						}
					} else {
						$booking = \App\Bookings::find($bookingId);
						if ($booking && $live_booking_id) {
							$booking->live_id = $live_booking_id;
							$booking->save();
						}
						$booking_payment = \App\BookingPayments::find($bookingPaymentId);
						if ($booking_payment && $live_booking_payment_id) {
							$booking_payment->live_id = $live_booking_payment_id;
							$booking_payment->save();
						}
					}
				} else {
					$bookingId = $booking->id;
					$booking->confidence = $device_booking->confidence;
					$booking->country_code = $device_booking->country_code;
					$booking->image_path = $device_booking->file_path;
					$booking->save();
				}
			} else {
				$bookingId = $booking->id;
				$booking->confidence = $device_booking->confidence;
				$booking->country_code = $device_booking->country_code;
				$booking->image_path = $device_booking->file_path;
				$booking->save();
			}


			$attendant = \App\Attendants::where('booking_id', $bookingId)->first();
			if (!$attendant) {
				$attendant = new \App\Attendants();
			}
			$attendant->booking_id = $bookingId;
			$attendant->save();
			$attendant_transaction_exist = \App\AttendantTransactions::where('attendant_id', $attendant->id)
							->whereDate('check_in', \Carbon\Carbon::today())
							->whereNull('check_out')->first();
			if ($attendant_transaction_exist) {
				return $booking;
			}
			$attendant_id = $attendant->id;
			\App\AttendantTransactions::where('attendant_id', $attendant_id)
					->whereNull('check_out')
					->update(['check_out' => date('Y-m-d H:i:s')]);
			$attendant_transaction = new \App\AttendantTransactions();
			$attendant_transaction->attendant_id = $attendant_id;
			$attendant_transaction->check_in = date('Y-m-d H:i:s', strtotime($device_booking->created_at));
			$attendant_transaction->save();
			$transaction_images = new \App\TransactionImages();
			$transaction_images->image_path = $device_booking->file_path;
			$transaction_images->device_id = $device_booking->device_id;
			$transaction_images->transaction_id = $attendant_transaction->id;
			$transaction_images->type = 'in';
			$transaction_images->save();
			$device_booking->delete();
			return $booking;
		}
		catch(\Exception $ex){
			$error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('Is_Booking_missed', $ex->getMessage(), $ex->getTraceAsString());
			return FALSE;
		}
    }

    public function verify_plate_num(Request $request, $key, $id, $vehicle, $confidence, $country_code = NULL) {
        $unwanted_array = array(
            'ü' => 'U', 
            'ö' => 'O', 
            'ä' => 'A',
            'Ü' => 'U',
            'Ö' => 'O',
            'Ä' => 'A'
        );
        $vehicle = strtr($vehicle, $unwanted_array);
        $settings = new Settings();
        try {
            $file_path = NULL;
            $this->set_lang_id($country_code);
            $valid_settings = $this->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown', '', $this->lang_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'od_sent' => $settings->send_message_od($id, $message, 'unknown'),
                    'data' => FALSE,
                );
            }
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extension = $file->extension() ? : 'png';
                $destinationPath = public_path('/uploads/vehicles');
                $safeName = str_random(10) . '.' . $extension;
                $file->move($destinationPath, $safeName);
                $request['pic'] = $safeName;
                $file_path = '/uploads/vehicles' . '/' . $safeName;
                $request->session()->put('vehicle_image', '/uploads/vehicles' . '/' . $safeName);
            }
            if ($valid_settings->has_related_ticket_reader) {
                $this->set_temporary_booking_entry($valid_settings->id, $vehicle, $confidence, $file_path, $country_code);
                $open_gate_controller = new OpenGateController();
                $status_emergency = $open_gate_controller->handle_emergency_entry_exit($valid_settings, $vehicle);
                if (!$status_emergency) {
                    $message = $this->ticket_reader->get_error_message('group_device_access_denied', '', $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'group_device_access_denied'),
                        'data' => FALSE,
                    );
                }
                $ip = $valid_settings->device_ip;
                $port = $valid_settings->device_port;
//                    Artisan::call('command:ReadyForRecognition', [
//                        'device' => $related_plate_reader->device_id
//                    ]);
                $client = new \App\Http\Controllers\Connection\Client($ip, $port);

                $key = $this->location_created_at . '-' . $valid_settings->id;
                $command = 'ready_recognition';
                $data = '36|' . $key;
                $client->send($command, $data);

                $message = 'Gate is opening';
                return array(
                    'status' => 1,
                    'access_status' => 'success',
                    'message' => $message,
                    'od_sent' => $settings->send_message_od($id, $message, 'welcome_entrance'),
                    'data' => FALSE,
                );
            }
            $request->session()->put('device_id', $valid_settings->id);
            $this->set_device_confidence($valid_settings->id);
            $confidence_status = $this->check_confidence($confidence);
            if (!$confidence_status) {
                $input_showed = $this->show_input_ticket_reader($id, $file_path);
                if (!$input_showed) {
                    $message = $this->ticket_reader->get_error_message('ticket_reader_not_configured', '', $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'error',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'ticket_reader_not_configured'),
                        'data' => FALSE,
                    );
                }
                $this->set_temporary_booking_entry($valid_settings->id, $vehicle, $confidence, $file_path, $country_code);
                $message = $this->ticket_reader->get_error_message('goto_nearby_ticket_reader', '', $this->lang_id);
                //     return array(
                //         'status' => 1,
                //         'access_status' => 'processing',
                //         'message' => $message,
                //         'od_sent' => $settings->send_message_od($id, $message, 'goto_nearby_ticket_reader'),
                //         'data' => FALSE,
                //     );
            } else {
                $this->set_temporary_booking_entry($valid_settings->id, $vehicle, $confidence, $file_path, $country_code);
            }
            $is_vehicle_blocked = $this->is_vehicle_blocked($vehicle);
            if ($is_vehicle_blocked) {
                $message = $this->ticket_reader->get_error_message('user_blocked', '', $this->lang_id);
                $this->send_denied_access_socket($valid_settings, $message, $vehicle);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'od_sent' => $settings->send_message_od($id, $message, 'user_blocked'),
                    'data' => FALSE,
                );
            }
            $vehicle_booking_data = FALSE;
            if ($valid_settings->device_direction == 'in') {
                $vehicle_booking = $this->get_vehicle_booking($vehicle, 'in');
                if ($valid_settings->barrier_status == 3 || (!$valid_settings->has_gate && isset($valid_settings->device_ticket_reader) && $valid_settings->device_ticket_reader->barrier_status == 3)) {
                    if (!$vehicle_booking) {
                        $vehicle_booking = $this->create_booking($vehicle, $valid_settings->id, NULL, false);
                    }
                    //$user_name = $this->ticket_reader->get_user_name($vehicle_booking);
                    $message = $this->ticket_reader->get_error_message('welcome_entrance', "", $this->lang_id);
                    $open_gate = $this->open_gate_plate_reader($valid_settings, $vehicle, $message, 'entry');
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'welcome_entrance'),
                        'data' => FALSE,
                        'vehicle_booking_data' => $vehicle_booking
                    );
                }
                if ($vehicle_booking) {
                    $vehicle_booking_data = $vehicle_booking;
                    $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
//                    $at_location = $this->settings->is_booking_at_location($vehicle_booking->id);
//                    if ($at_location) {
//                        $message = $this->ticket_reader->get_error_message('already_at_location', $user_name, $this->lang_id);
//                        Artisan::call('command:DeniedAccess', [
//                            'device' => $id, 'message' => $message, 'vehicle_num' => $vehicle
//                        ]);
//                        $this->send_denied_access_socket($valid_settings,$message,$vehicle);
//                        return array(
//                            'status' => 1,
//                            'access_status' => 'denied',
//                            'message' => $message,
//                            'data' => FALSE,
//                        );
//                    }
                    $message = $this->ticket_reader->get_error_message('welcome_entrance', $user_name, $this->lang_id);
                    $open_gate = $this->open_gate_plate_reader($valid_settings, $vehicle, $message, 'entry');
                    if (!$open_gate && !$valid_settings->has_gate) {
                        $message = $this->ticket_reader->get_error_message('ticket_reader_not_configured', '', $this->lang_id);
                        return array(
                            'status' => 1,
                            'access_status' => 'error',
                            'message' => $message,
                            'od_sent' => $settings->send_message_od($id, $message, 'ticket_reader_not_configured'),
                            'data' => FALSE,
                        );
                    }
                    //  $message = 'Gate is opening';
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'welcome_entrance'),
                        'data' => FALSE,
                        'vehicle_booking_data' => $vehicle_booking_data
                    );
                }
                if (!$confidence_status) {
                    $message = $this->ticket_reader->get_error_message('goto_nearby_ticket_reader', '', $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'processing',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'goto_nearby_ticket_reader'),
                        'data' => FALSE,
                    );
                }
                $message = $this->ticket_reader->get_error_message('welcome_entrance', '', $this->lang_id);
                $open_gate = $this->open_gate_plate_reader($valid_settings, $vehicle, $message, 'entry');
                if (!$open_gate && !$valid_settings->has_gate) {
                    $message = $this->ticket_reader->get_error_message('ticket_reader_not_configured', '', $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'error',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'ticket_reader_not_configured'),
                        'data' => FALSE,
                    );
                }
                return array(
                    'status' => 1,
                    'access_status' => 'success',
                    'message' => $message,
                    'od_sent' => $settings->send_message_od($id, $message, 'welcome_entrance'),
                    'data' => FALSE,
                );
            } elseif ($valid_settings->device_direction == 'out') {
                $vehicle_booking = $this->get_vehicle_booking($vehicle, 'out');

                if ($valid_settings->barrier_status == 3 || (!$valid_settings->has_gate && isset($valid_settings->device_ticket_reader) && $valid_settings->device_ticket_reader->barrier_status == 3)) {
                    if (!$vehicle_booking) {
                        $vehicle_booking = $this->create_booking($vehicle, $valid_settings->id, NULL, false);
                    }
                    $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
                    $message = $this->ticket_reader->get_error_message('goodbye_exit', $user_name, $this->lang_id);
                    $open_gate = $this->open_gate_plate_reader($valid_settings, $vehicle, $message, 'exit');
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'welcome_entrance'),
                        'data' => FALSE,
                        'vehicle_booking_data' => $vehicle_booking
                    );
                }
                if (!$vehicle_booking) {
                    if (!$confidence_status) {
                        $message = $this->ticket_reader->get_error_message('goto_nearby_ticket_reader', '', $this->lang_id);
                        return array(
                            'status' => 1,
                            'access_status' => 'processing',
                            'message' => $message,
                            'od_sent' => $settings->send_message_od($id, $message, 'goto_nearby_ticket_reader'),
                            'data' => FALSE,
                        );
                    }
                    $message = $this->ticket_reader->get_error_message('system_error', $vehicle, $this->lang_id);
//                    Artisan::call('command:DeniedAccess', [
//                        'device' => $id, 'message' => $message, 'vehicle_num' => $vehicle
//                    ]);
                    $this->send_denied_access_socket($valid_settings, $message, $vehicle);
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'system_error'),
                        'data' => FALSE,
                    );
                }
                $vehicle_booking_data = $vehicle_booking;
                $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
                $at_location = true; //$this->settings->is_booking_at_location($vehicle_booking->id);
                if (!$at_location && !($vehicle_booking->type == 3 || $vehicle_booking->type == 2)) {
                    $message = $this->ticket_reader->get_error_message('system_error', $vehicle, $this->lang_id);
//                    Artisan::call('command:DeniedAccess', [
//                        'device' => $id, 'message' => $message, 'vehicle_num' => $vehicle
//                    ]);
                    $this->send_denied_access_socket($valid_settings, $message, $vehicle);
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'system_error'),
                        'data' => FALSE,
                        'vehicle_booking_data' => $vehicle_booking_data
                    );
                }
                $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
                $is_paid = $this->is_booking_paid($vehicle_booking);

                if ($is_paid['status']) {
                    $message = $this->ticket_reader->get_error_message('goodbye_exit', $user_name, $this->lang_id);

                    $open_gate = $this->open_gate_plate_reader($valid_settings, $vehicle, $message, 'exit');
                    $message = $this->ticket_reader->get_error_message('goodbye_exit', $user_name, $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'goodbye_exit'),
                        'data' => $vehicle_booking->id,
                        'vehicle_booking_data' => $vehicle_booking_data
                    );
                }
                if (!$confidence_status) {
                    $message = $this->ticket_reader->get_error_message('goto_nearby_ticket_reader', '', $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'processing',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'goto_nearby_ticket_reader'),
                        'data' => FALSE,
                        'vehicle_booking_data' => $vehicle_booking_data
                    );
                }
                $message = $is_paid['message'];
//                Artisan::call('command:DeniedAccess', [
//                    'device' => $id, 'message' => $message, 'vehicle_num' => $vehicle
//                ]);
                $this->send_denied_access_socket($valid_settings, $message, $vehicle);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'od_sent' => $settings->send_message_od($id, $message, 'unknown'),
                    'data' => $vehicle_booking->id,
                    'vehicle_booking_data' => $vehicle_booking_data
                );
            } else {
                $message = 'Bidirectional Devices is not supported';
                return array(
                    'status' => 1,
                    'access_status' => 'error',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('vehicle-verify', $ex->getMessage(), $ex->getTraceAsString());
            $message = $this->ticket_reader->get_error_message('unknown', '', $this->lang_id);
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $message,
                'od_sent' => $settings->send_message_od($id, $message, 'unknown'),
                'data' => FALSE,
            );
        }
    }

    public function is_valid_call($key, $id) {
        $key_array = explode('-', $key);
        if (count($key_array) != 2) {
            return FALSE;
        }
        $location_settings = new \App\Http\Controllers\Settings\LocationSettings();
        $location = $location_settings->get_location();
        if (strtotime($location->created_at) != $key_array[0]) {
            return FALSE;
        }
        if ($id == null) {
            $id = $key_array[1];
        }
        $location_device = \App\LocationDevices::find($id);
        if (!$location_device) {
            return FALSE;
        }
        $device_ticket_readers = \App\DeviceTicketReaders::where('device_id', $id)->first();
        if ($device_ticket_readers) {
            $ticket_reader = \App\LocationDevices::find($device_ticket_readers->ticket_reader_id);
            if ($ticket_reader) {
                $location_device->device_ticket_reader = $ticket_reader;
            }
        }
        return $location_device;
    }

    public function check_confidence($confidence) {
        if ($confidence >= $this->confidence_val) {
            return TRUE;
        }
        return FALSE;
    }

    public function show_input_ticket_reader($device_id, $file_path) {
        $related_ticket_reader = \App\DeviceTicketReaders::where([
                    ['device_id', $device_id]
                ])->first();
        if (!$related_ticket_reader) {
            return FALSE;
        }
        $ticket_reader_details = \App\LocationDevices::find($related_ticket_reader->ticket_reader_id);
        if (!$ticket_reader_details) {
            return FALSE;
        }
        if (!$ticket_reader_details->is_synched) {
            return FALSE;
        }
        $ip = $ticket_reader_details->device_ip;
        $port = $ticket_reader_details->device_port;
        if (empty($ip) || empty($port)) {
//            $ticket_reader_details->is_synched = 0;
//            $ticket_reader_details->save();
            return FALSE;
        }
//        Artisan::call('command:ShowTicketReaderInput', [
//            'device' => $ticket_reader_details->id
//        ]);
        $client = new \App\Http\Controllers\Connection\Client($ip, $port);
        $key = $this->location_created_at . '-' . $ticket_reader_details->id;
        $command = 'ShowTicketReaderInput';
        $data = '32|' . $key . '|' . '1' . '|' . $file_path;
        $client->send($command, $data);
        return TRUE;
    }

    public function get_vehicle_booking($vehicle_num, $status) {
        try {
            $valid_bookings_types = array(1, 4, 7, 10);
            if ($status == 'in') {
                $user_list_statuses = array(2, 3);
                $userlist_booking_details = \App\Bookings::where([
                            ['vehicle_num', $vehicle_num]
                        ])
                        ->whereIn('type', $user_list_statuses)
                        ->first();
                if ($userlist_booking_details) {
                    if ($userlist_booking_details->customer_id > 0) {
                        $userlist_user = \App\UserlistUsers::where('customer_id', $userlist_booking_details->customer_id)->first();
                        if ($userlist_user) {
                            $this->lang_id = $userlist_user->language_id;
                        }
                    }
                    return $userlist_booking_details;
                }
                $booking_details = \App\Bookings::where([
                            ['vehicle_num', $vehicle_num]
                        ])
                        ->whereIn('type', $valid_bookings_types)
                        ->where('checkout_time', '>', date('Y-m-d H:i:s'))
                        ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
                        ->orderBy('created_at', 'DESC')
                        ->first();
                if ($booking_details) {
                    //if ($booking_details->checkout_time > date('Y-m-d H:i', strtotime('-' . $this->lag_time . ' minutes', strtotime(date("Y-m-d H:i"))))) {
                    return $booking_details;
                    //}
                }
                return FALSE;
            } elseif ($status == 'out') {
                $user_list_statuses = array(2, 3);
                $userlist_booking_details = \App\Bookings::where([
                            ['vehicle_num', $vehicle_num]
                        ])
                        ->whereIn('type', $user_list_statuses)
                        ->first();
                if ($userlist_booking_details) {
                    if ($userlist_booking_details->customer_id > 0) {
                        $userlist_user = \App\UserlistUsers::where('customer_id', $userlist_booking_details->customer_id)->first();
                        if ($userlist_user) {
                            $this->lang_id = $userlist_user->language_id;
                        }
                    }
                    return $userlist_booking_details;
                }
				$booking_details = \App\Bookings::where([
                            ['vehicle_num', $vehicle_num]
                        ])
                        ->whereIn('type', $valid_bookings_types)
                        ->where('checkout_time', '>', date('Y-m-d H:i:s'))
                        ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
                        ->orderBy('is_paid', 'DESC')
                        ->first();
				if ($booking_details) {
                    return $booking_details;
                }
                $booking_details = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                                    $query->whereNull('check_out');
                                })->whereIn('type', $valid_bookings_types)
                                ->where('vehicle_num', $vehicle_num)->first();
                if ($booking_details) {
                    return $booking_details;
                }
                $is_booking_missed = $this->check_is_booking_missed($vehicle_num);
                if ($is_booking_missed) {
                    return $is_booking_missed;
                }
                return FALSE;
            } else {
                return FALSE;
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('get_vehicle_booking', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function is_vehicle_blocked($vehicle_num) {
        $vehicle_blocked = FALSE;
        $userlist_users = \App\UserlistUsers::whereHas('customer_vehicle_info', function ($query) use ($vehicle_num) {
                    $query->where('num_plate', $vehicle_num);
                })->where('is_blocked', 1)->first();

        if ($userlist_users) {
            $vehicle_blocked = TRUE;
        }
        return $vehicle_blocked;
    }

    public function open_gate_plate_reader($ticket_reader_details, $vehicle_num, $message, $type) {
        try {
            $open_gate_call_start = microtime(true);
            if ($ticket_reader_details->available_device_id == 3) {
                $related_ticket_reader = \App\DeviceTicketReaders::where([
                            ['device_id', $ticket_reader_details->id]
                        ])->first();

                if (!$related_ticket_reader) {
                    return FALSE;
                }
                $related_ticket_reader_id = $related_ticket_reader->ticket_reader_id;
                $ticket_reader_details = \App\LocationDevices::find($related_ticket_reader_id);
            }

            if (!$ticket_reader_details) {
                return FALSE;
            }
            if (!$ticket_reader_details->is_synched) {
                return FALSE;
            }
            $ip = $ticket_reader_details->device_ip;
            $port = $ticket_reader_details->device_port;
            if (empty($ip) || empty($port)) {
                $ticket_reader_details->is_synched = 0;
                $ticket_reader_details->save();
                return FALSE;
            }
            if ($type == 'entry') {
                if (empty($message)) {
                    $message = $this->ticket_reader->get_error_message('welcome_entrance', '', $this->lang_id);
                }
                if ($ticket_reader_details->barrier_status != 1 && $ticket_reader_details->barrier_status != 2) {
//                Artisan::call('command:OpenGateForVehicle', [
//                    'device' => $ticket_reader_details->id, 'vehicle' => $vehicle_num, 'message' => $message
//                ]);
                    $client = new \App\Http\Controllers\Connection\Client($ip, $port);

                    $key = $this->location_created_at . '-' . $ticket_reader_details->id;
                    $command = 'open_gate';
                    $data = '31|' . $key . '|' . $vehicle_num . '|' . $message;
                    $open_gate_call_total_time_start = (round(microtime(true) - $open_gate_call_start, 3) * 1000);

                    $connection = $client->send($command, $data);
                    $open_gate_call_total_time_after = (round(microtime(true) - $open_gate_call_start, 3) * 1000);

                    \Illuminate\Support\Facades\Session::put('open_gate_call_total_time_start', $open_gate_call_total_time_start);
                    \Illuminate\Support\Facades\Session::put('open_gate_call_total_time_after', $open_gate_call_total_time_after);

                    if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                        if ($connection['status'] >= 3) {
                            $ticket_reader_details->is_synched = 1;
                            $ticket_reader_details->is_opened = 1;
                            $ticket_reader_details->save();
                        }
                    }
                }
            } elseif ($type == 'exit') {
                if (empty($message)) {
                    $message = $this->ticket_reader->get_error_message('goodbye_exit', '', $this->lang_id);
                }
                if ($ticket_reader_details->barrier_status != 1 && $ticket_reader_details->barrier_status != 2) {
//                Artisan::call('command:OpenGateForExitVehcile', [
//                    'device' => $ticket_reader_details->id, 'vehicle' => $vehicle_num, 'message' => $message
//                ]);
                    $client = new \App\Http\Controllers\Connection\Client($ip, $port);
                    $key = $this->location_created_at . '-' . $ticket_reader_details->id;
                    $command = 'open_gate_Exit';
                    $data = '35|' . $key . '|' . $vehicle_num . '|' . $message;
                    $connection = $client->send($command, $data);
                    if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                        if ($connection['status'] >= 3) {
                            $ticket_reader_details->is_synched = 1;
                            $ticket_reader_details->is_opened = 1;
                            $ticket_reader_details->save();
                        }
                    }
                }
            }
            return TRUE;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function verify_plate_num_status(Request $request, $key, $id, $vehicle, $status, $booking = NULL) {
        if ($booking == 0) {
            $booking = NULL;
        }
        $settings = new Settings();
        try {
            $valid_settings = $this->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown', '', $this->lang_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'od_sent' => $settings->send_message_od($id, $message, 'unknown'),
                    'data' => FALSE,
                );
            }
            $related_plate_reader = \App\DeviceTicketReaders::where([
                        ['ticket_reader_id', $id]
                    ])->first();
            if ($related_plate_reader) {
                $device = \App\LocationDevices::find($related_plate_reader->device_id);
                if ($device) {

                    $ip = $device->device_ip;
                    $port = $device->device_port;
//                    Artisan::call('command:ReadyForRecognition', [
//                        'device' => $related_plate_reader->device_id
//                    ]);
                    $client = new \App\Http\Controllers\Connection\Client($ip, $port);

                    $key = $this->location_created_at . '-' . $device->id;
                    $command = 'ready_recognition';
                    $data = '36|' . $key;
                    $client->send($command, $data);
                }
            }
            if ($valid_settings->barrier_status != 1 && $valid_settings->barrier_status != 2) {
                if ($valid_settings->is_opened == 1) {
                    $valid_settings->is_opened = 0;
                    $valid_settings->save();
                }
            }
            if ($valid_settings->barrier_status == 3) {
                $open_gate_reason = 'always_access';
                \Illuminate\Support\Facades\Session::put('open_gate_reason', $open_gate_reason);
            }
            if ($status) {
                if ($valid_settings->device_direction == 'in') {
                    $vehicle_booking = $this->get_vehicle_booking($vehicle, 'in');
                    if ($vehicle_booking) {
                        $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
//                        $at_location = $this->settings->is_booking_at_location($vehicle_booking->id);
//                        if ($at_location) {
//                            $message = $this->ticket_reader->get_error_message('already_at_location', $user_name, $this->lang_id);
//                            return array(
//                                'status' => 1,
//                                'access_status' => 'denied',
//                                'message' => $message,
//                                'data' => FALSE,
//                            );
//                        }
                        $this->set_booking_entry($vehicle_booking, $valid_settings->id);
                        if ($vehicle_booking->user_arrival_notification) {
                            $this->user_arrival_notification($vehicle_booking, 'in');
                        }
                        $message = $this->ticket_reader->get_error_message('welcome_entrance', $user_name, $this->lang_id);
                        if ($valid_settings->barrier_status != 1 && $valid_settings->barrier_status != 2) {
                            sleep(2);
                            // Artisan::call('command:CloseTicketReader', [
                            //      'device' => $valid_settings->id
                            //  ]);
                        }
                        return array(
                            'status' => 1,
                            'access_status' => 'success',
                            'message' => $message,
                            'od_sent' => $settings->send_message_od($id, $message, 'welcome_entrance'),
                            'data' => FALSE,
                        );
                    }
                    $message = $this->ticket_reader->get_error_message('welcome_entrance', '', $this->lang_id);
                    $this->create_booking($vehicle, $valid_settings->id);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'welcome_entrance'),
                        'data' => FALSE,
                    );
                } elseif ($valid_settings->device_direction == 'out') {
                    $vehicle_booking = $this->get_vehicle_booking($vehicle, 'out');
                    if (!$vehicle_booking) {
                        $message = $this->ticket_reader->get_error_message('Unauthorized', '', $this->lang_id);
                        return array(
                            'status' => 1,
                            'access_status' => 'denied',
                            'message' => $message,
                            'od_sent' => $settings->send_message_od($id, $message, 'Unauthorized'),
                            'data' => FALSE,
                        );
                    }
                    if ($booking != NULL) {
                        $vehicle_booking = $this->set_vehicle_booking_checkout($vehicle_booking, $booking, 'out');
                    }
                    $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
                    //$at_location = $this->settings->is_booking_at_location($vehicle_booking->id);
//                    if (!$at_location) {
//                        $message = $this->ticket_reader->get_error_message('Unauthorized', $user_name, $this->lang_id);
//                        return array(
//                            'status' => 1,
//                            'access_status' => 'denied',
//                            'message' => $message,
//                            'data' => FALSE,
//                        );
//                    }
                    $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
                    if (!$valid_settings->has_gate && isset($valid_settings->device_ticket_reader)) {
                        $device_ticket_reader = $valid_settings->device_ticket_reader;
                        if ($device_ticket_reader->barrier_status == 3) {
                            $open_gate_reason = 'always_access';
                            \Illuminate\Support\Facades\Session::put('open_gate_reason', $open_gate_reason);
                        }
                    }
                    $this->set_booking_exit($vehicle_booking, $valid_settings->id);
                    $message = $this->ticket_reader->get_error_message('goodbye_exit', $user_name, $this->lang_id);
                    if ($vehicle_booking->user_arrival_notification) {
                        $this->user_arrival_notification($vehicle_booking, 'out');
                    }
                    if ($valid_settings->barrier_status != 1 && $valid_settings->barrier_status != 2) {
                        //sleep(2);
                        //Artisan::call('command:CloseTicketReader', [
                        //    'device' => $valid_settings->id
                        //]);
                    }
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'goodbye_exit'),
                        'data' => FALSE,
                    );
                } else {
                    $message = 'Bidirectional Devices is not supported';
                    return array(
                        'status' => 1,
                        'access_status' => 'error',
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
            }
            $message = $this->ticket_reader->get_error_message('unknown', '', $this->lang_id);
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'message' => $message,
                'od_sent' => $settings->send_message_od($id, $message, 'unknown'),
                'data' => FALSE,
            );
        } catch (\Exception $ex) {

            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('vehicle-verify-status', $ex->getMessage(), $ex->getTraceAsString());
            $message = $this->ticket_reader->get_error_message('unknown');
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $message,
                'od_sent' => $settings->send_message_od($id, $message, 'unknown'),
                'data' => FALSE,
            );
        }
    }

    public function set_booking_entry($booking_details, $device_id) {
        $attendant = \App\Attendants::where('booking_id', $booking_details->id)->first();
        if (!$attendant) {
            $attendant = new \App\Attendants();
        }
        $attendant->booking_id = $booking_details->id;
        $attendant->save();
        $attendant_id = $attendant->id;
        $attendants = array();
        $existing_checked_in_bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                            $query->whereNull('check_out');
                        })
                        ->where('vehicle_num', $booking_details->vehicle_num)->get();
        foreach ($existing_checked_in_bookings as $booking_close) {
            $attendants[] = $booking_close->attendants->id;
            if ($booking_close->checkout_time == null) {
                $booking_close->checkout_time = date('Y-m-d H:i:s');
                $booking_close->save();
            }
        }
        \App\AttendantTransactions::whereIn('attendant_id', $attendants)
                ->whereNull('check_out')
                ->update(['check_out' => date('Y-m-d H:i:s')]);
        $attendant_transaction = new \App\AttendantTransactions();
        $attendant_transaction->attendant_id = $attendant_id;
        $attendant_transaction->check_in = date('Y-m-d H:i:s');
        $attendant_transaction->save();
        $this->update_transaction_table($device_id, $attendant_transaction->id, 'in');
        \Illuminate\Support\Facades\Session::forget('vehicle_image');
        $this->update_booking_from_temporary_booking($device_id, $booking_details->id);
        try {
            if (!$this->key) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('import-key', 'custom: Import key not found');
                return FALSE;
            }
            $Key = $this->key;
            $dataArray = array(
                'booking_info_live_id' => $booking_details->live_id,
                'checkin_time' => date('Y-m-d H:i:s'),
                'vehicle_num' => $booking_details->vehicle_num,
                'attendant' => 1
            );
            $http = new Client();
            $response = $http->post($this->url . '/api/store-booking-info', [
                'form_params' => [
                    'token' => $Key,
                    'data' => $dataArray
                ],
            ]);
            $responseData = json_decode((string) $response->getBody(), true);
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('set_booking_exit', $ex->getMessage(), $ex->getTraceAsString());
        }
        return TRUE;
    }

    public function set_booking_exit($booking_details, $device_id) {
        if (empty($booking_details->checkout_time)) {
            $booking_details->checkout_time = date('Y-m-d H:i:s');
            $booking_details->save();
        }
        try {
            if (!$this->key) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('import-key', 'custom: Import key not found');
                return FALSE;
            }
            $Key = $this->key;
            $dataArray = array(
                'booking_info_live_id' => $booking_details->live_id,
                'checkout_time' => date('Y-m-d H:i:s'),
                'vehicle_num' => $booking_details->vehicle_num,
                'attendant' => 1
            );
            $http = new Client();
            $response = $http->post($this->url . '/api/store-booking-info', [
                'form_params' => [
                    'token' => $Key,
                    'data' => $dataArray
                ],
            ]);
            $responseData = json_decode((string) $response->getBody(), true);
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('set_booking_exit', $ex->getMessage(), $ex->getTraceAsString());
        }
        $attendant = \App\Attendants::where('booking_id', $booking_details->id)->first();
        if (!$attendant) {
            $attendant = new \App\Attendants();
        }
        $attendant->booking_id = $booking_details->id;
        $attendant->save();
        $attendant_id = $attendant->id;
        $attendant_transaction = \App\AttendantTransactions::where('attendant_id', $attendant_id)
                ->orderBy('created_at', 'desc')
                ->first();
        if (!$attendant_transaction) {
            $attendant_transaction = new \App\AttendantTransactions();
            $attendant_transaction->attendant_id = $attendant_id;
            $attendant_transaction->check_in = date('Y-m-d H:i:s');
            $attendant_transaction->check_out = date('Y-m-d H:i:s');
            $attendant_transaction->save();
        } else {
            $attendant_transaction->attendant_id = $attendant_id;
            $attendant_transaction->check_out = date('Y-m-d H:i:s');
            $attendant_transaction->save();
        }
        $this->update_transaction_table($device_id, $attendant_transaction->id, 'out');
        \Illuminate\Support\Facades\Session::forget('vehicle_image');
        $this->update_booking_from_temporary_booking($device_id, $booking_details->id);
        $attendants = array();
        $existing_checked_in_bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                            $query->whereNull('check_out');
                        })
                        ->where('vehicle_num', $booking_details->vehicle_num)->get();
        foreach ($existing_checked_in_bookings as $booking_close) {
            $attendants[] = $booking_close->attendants->id;
            if ($booking_close->checkout_time == null) {
                $booking_close->checkout_time = date('Y-m-d H:i:s');
                $booking_close->save();
            }
        }
        \App\AttendantTransactions::whereIn('attendant_id', $attendants)
                ->whereNull('check_out')
                ->update(['check_out' => date('Y-m-d H:i:s')]);
        return TRUE;
    }

    public function create_booking($vehicle, $device_id) {

        $dataArray = array(
            'first_name' => 'Paid Vehicle',
            'vehicle_num' => $vehicle,
            'type' => 4,
            'is_paid' => 0,
            'checkin_time' => date('Y-m-d H:i:s'),
            'amount' => 0,
            'payment_id' => 'Paid Vehicle',
            'attendant' => 1
        );
        $booking = new \App\Bookings();
        $booking->type = $dataArray['type'];
        $booking->first_name = $dataArray['first_name'];
        $booking->vehicle_num = $dataArray['vehicle_num'];
        $booking->checkin_time = $dataArray['checkin_time'];
        $booking->save();
        $bookingId = $booking->id;
        $booking_payment = new \App\BookingPayments();
        $booking_payment->booking_id = $bookingId;
        $booking_payment->amount = $dataArray['amount'];
        $booking_payment->payment_id = $dataArray['payment_id'];
        $booking_payment->checkin_time = $dataArray['checkin_time'];
        $booking_payment->save();


        $bookingPaymentId = $booking_payment->id;

        try {
            if (!$this->key) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('import-key', 'custom: Import key not found');
                return FALSE;
            }
            $Key = $this->key;
            $http = new Client();
            $response = $http->post($this->url . '/api/store-booking-info', [
                'form_params' => [
                    'token' => $Key,
                    'data' => $dataArray
                ],
            ]);
            $responseData = json_decode((string) $response->getBody(), true);
            if (is_array($responseData) && array_key_exists('booking_info_live_id', $responseData['data'])) {
                $booking = \App\Bookings::find($bookingId);
                if ($booking) {
                    $booking->live_id = $responseData['data']['booking_info_live_id'];
                    $booking->save();
                }
            }
            if (is_array($responseData) && array_key_exists('booking_payment_live_id', $responseData['data'])) {
                $booking_payment = \App\BookingPayments::find($bookingPaymentId);
                if ($booking_payment) {
                    $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                    $booking_payment->save();
                }
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('get_vehicle_booking', $ex->getMessage(), $ex->getTraceAsString());
        }
        $this->set_booking_entry($booking, $device_id);
    }

    public function is_booking_paid($booking) {
        if ($booking->is_paid) {
            if ($booking->type == 2 || $booking->type == 3) {
                return array(
                    'status' => 1,
                    'message' => 'Thanks',
                );
            }
            if (empty($booking->checkout_time)) {
                $booking_payment = $booking->booking_payments;
                if ($booking_payment) {
                    if (date('Y-m-d H:i') <= date('Y-m-d H:i', strtotime($booking_payment->checkout_time))) {
                        $booking->checkout_time = $booking_payment->checkout_time;
                        $booking->save();
                        return array(
                            'status' => 1,
                            'message' => 'Thanks',
                        );
                    }
                }
                $booking_details = \App\Bookings::where([
                            ['vehicle_num', $booking->vehicle_num]
                        ])
                        ->where('type', 4)
                        ->where('checkout_time', '>', date('Y-m-d H:i'))
                        ->where('checkin_time', '<=', date('Y-m-d H:i'))
                        ->where('is_paid', 1)
                        ->orderBy('created_at', 'DESC')
                        ->first();
                if ($booking_details) {
                    return array(
                        'status' => 1,
                        'message' => 'Thanks',
                    );
                }
                return array(
                    'status' => 0,
                    'message' => $this->ticket_reader->get_error_message('goto_nearby_payment_terminal', '', $this->lang_id),
                );
            }
            if (date('Y-m-d H:i') > date('Y-m-d H:i', strtotime($booking->checkout_time))) {
                $booking_details = \App\Bookings::where([
                            ['vehicle_num', $booking->vehicle_num]
                        ])
                        ->where('type', 4)
                        ->where('checkout_time', '>', date('Y-m-d H:i'))
                        ->where('checkin_time', '<=', date('Y-m-d H:i'))
                        ->where('is_paid', 1)
                        ->orderBy('created_at', 'DESC')
                        ->first();
                if ($booking_details) {
                    return array(
                        'status' => 1,
                        'message' => 'Thanks',
                    );
                }
                return array(
                    'status' => 0,
                    'message' => $this->ticket_reader->get_error_message('goto_nearby_payment_terminal', '', $this->lang_id),
                );
            }
            return array(
                'status' => 1,
                'message' => 'Thanks',
            );
        }
        $booking_details = \App\Bookings::where([
                    ['vehicle_num', $booking->vehicle_num]
                ])
                ->where('type', 4)
                ->where('checkout_time', '>', date('Y-m-d H:i'))
                ->where('checkin_time', '<=', date('Y-m-d H:i'))
                ->where('is_paid', 1)
                ->orderBy('created_at', 'DESC')
                ->first();
        if ($booking_details) {
            return array(
                'status' => 1,
                'message' => 'Thanks',
            );
        }
        return array(
            'status' => 0,
            'message' => $this->ticket_reader->get_error_message('goto_nearby_payment_terminal', '', $this->lang_id)
        );
    }

    public function set_device_confidence($device_id) {
        $device_details = \App\LocationDevices::find($device_id);
        if ($device_details) {
            if (!empty($device_details->confidence) && is_numeric($device_details->confidence)) {
                $this->confidence_val = $device_details->confidence;
            }
        }
    }

    public function set_lang_id($country_code) {
        if ($country_code == NULL) {
            
        }
        $lang_details = \App\Language::where('code', $country_code)->first();
        if ($lang_details) {
            $this->lang_id = $lang_details->id;
        }
    }

    public function set_temporary_booking_entry($device_id, $vehicle_num, $confidence, $file_path, $country_code) {
        // available device id check only for plate reader
        $booking = new \App\DeviceBookings();
        $booking->device_id = $device_id;
        $booking->vehicle_num = $vehicle_num;
        $booking->confidence = $confidence;
        $booking->file_path = $file_path;
        $booking->country_code = $country_code;
        $booking->save();
    }

    public function update_booking_from_temporary_booking($device_id, $booking_id) {
        $related_plate_reader = \App\DeviceTicketReaders::where([
                    ['ticket_reader_id', $device_id]
                ])->first();
        if (!$related_plate_reader) {
            return FALSE;
        }
        $low_confidence = 0;
        $related_plate_reader_id = $related_plate_reader->device_id;
        $device_details = \App\LocationDevices::find($related_plate_reader_id);
        if (!$device_details) {
            return FALSE;
        }
        $booking = \App\Bookings::find($booking_id);
        $temporary_booking = \App\DeviceBookings::where('device_id', $related_plate_reader_id)->where('vehicle_num', $booking->vehicle_num)
                ->orderBy('created_at', 'DESC')
                ->first();

        if (!$temporary_booking) {
            return FALSE;
        }
        if (!empty($device_details->confidence)) {
            if ($temporary_booking->confidence < $device_details->confidence) {
                $low_confidence = 1;
            }
        }
        $booking->confidence = $temporary_booking->confidence;
        $booking->low_confidence = $low_confidence;
        $booking->country_code = $temporary_booking->country_code;
        $booking->image_path = $temporary_booking->file_path;
        $booking->save();
        $temporary_booking->delete();
        \App\DeviceBookings::where('device_id', $related_plate_reader_id)
                ->where('is_operator', '1')
                ->delete();

        return TRUE;
    }

    public function update_transaction_table($device_id, $attendant_id, $status) {
        $related_plate_reader = \App\DeviceTicketReaders::where([
                    ['ticket_reader_id', $device_id]
                ])->first();

        if (!$related_plate_reader) {
            return FALSE;
        }
        $related_plate_reader_id = $related_plate_reader->device_id;
        $device_details = \App\LocationDevices::find($related_plate_reader_id);
        if (!$device_details) {
            return FALSE;
        }
        $temporary_booking = \App\DeviceBookings::where('device_id', $related_plate_reader_id)
                ->orderBy('created_at', 'DESC')
                ->first();
        if (!$temporary_booking) {
            return FALSE;
        }
        $transaction_images = new \App\TransactionImages();
        $transaction_images->image_path = $temporary_booking->file_path;
        $transaction_images->device_id = $temporary_booking->device_id;
        $transaction_images->transaction_id = $attendant_id;
        $transaction_images->type = $status;
        $transaction_images->save();

        if (Session::has('open_gate_reason')) {
            $open_gate_reason = Session::get('open_gate_reason');
            $manual_open_gate = new \App\OpenGateManualTransaction();
            $manual_open_gate->transaction_images_id = $transaction_images->id;
            $manual_open_gate->attendant_transaction_id = $attendant_id;
            if ($open_gate_reason == 'always_access') {
                $manual_open_gate->reason = 'Always Access';
                $manual_open_gate->user_id = $device_details->user_id ? : 1;
                $manual_open_gate->type = 'AA';
            } else {
                $manual_open_gate->reason = $open_gate_reason;
                $manual_open_gate->user_id = \Illuminate\Support\Facades\Auth::id();
            }
            $manual_open_gate->location_device_id = $device_id;
            $manual_open_gate->save();

            Session::forget('open_gate_reason');
        }
    }

    public function set_vehicle_booking_checkout($vehicle_booking, $booking, $status) {
        try {
            if ($status == 'out') {
                $valid_bookings_types = array(1, 2, 3, 4, 7);
                $booking_details = \App\Bookings::where([
                            ['live_id', $booking]
                        ])
                        ->whereIn('type', $valid_bookings_types)
                        ->first();
                $promoCode = NULL;
                if ($booking_details) {
                    if ($booking_details->type == 4) {
                        $promoCode = $booking_details->promo_code;
                        if ($booking_details->promo_code != NULL) {
                            $promo = \App\Promo::where('code', $booking_details->promo_code)->first();
                            if ($promo) {
                                if ($promo->end_date != Null && $promo->coupon_number_limit == Null) {
                                    if (strtotime($promo->end_date) < strtotime(date("Y-m-d h:i:s"))) {
                                        return $vehicle_booking;
                                    } else if (strtotime($promo->start_date) > strtotime(date("Y-m-d h:i:s"))) {
                                        return $vehicle_booking;
                                    }
                                } else if ($promo->end_date == Null && $promo->coupon_number_limit != Null) {
                                    if ($promo->coupon_number_limit <= $promo->coupon_used) {
                                        return $vehicle_booking;
                                    }
                                }
                            }
                        }
                    }
                    $vehicle_booking_details = \App\Bookings::find($vehicle_booking->id);
                    if ($vehicle_booking_details) {
                        $data = array(
                            'booking_info_live_id' => $vehicle_booking_details->live_id,
                            'is_paid' => 1,
                            'checkout_time' => date('Y-m-d H:i:s'),
                            'promo_code' => $promoCode,
                            'attendant' => 1
                        );
                        if (!$this->key) {
                            $error_log = new \App\Http\Controllers\LogController();
                            $error_log->log_create('import-key', 'custom: Import key not found');
                            return FALSE;
                        }
                        $Key = $this->key;
                        $http = new Client();
                        $response = $http->post($this->url . '/api/store-booking-info', [
                            'form_params' => [
                                'token' => $Key,
                                'data' => $data
                            ],
                        ]);
                        $responseData = json_decode((string) $response->getBody(), true);
                        if (array_key_exists('booking_info_live_id', $responseData['data'])) {
                            $vehicle_booking_details->live_id = $responseData['data']['booking_info_live_id'];
                        }
                        $vehicle_booking_details->checkout_time = $data['checkout_time'];
                        $vehicle_booking_details->is_paid = $data['is_paid'];
                        if ($booking_details->type == 4 && $data['promo_code'] != NULL) {
                            $vehicle_booking_details->promo_code = $data['promo_code'];
                        }
                        $vehicle_booking_details->save();

                        $booking_payment = \App\BookingPayments::where('booking_id', $vehicle_booking_details->id)->first();
                        if ($booking_payment) {
                            if (array_key_exists('booking_payment_live_id', $responseData['data'])) {
                                $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                            }
                            $booking_payment->checkout_time = $data['checkout_time'];
                            $booking_payment->save();
                        }

                        $vehicle_booking->checkout_time = $vehicle_booking_details->checkout_time;
                        $vehicle_booking->is_paid = $vehicle_booking_details->is_paid;
                        if ($vehicle_booking_details->promo_code != NULL) {
                            $vehicle_booking->promo_code = $vehicle_booking_details->promo_code;
                        }

                        return $vehicle_booking;
                    }
                    return $vehicle_booking;
                }
                return $vehicle_booking;
            } else {
                return $vehicle_booking;
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('get_vehicle_booking', $ex->getMessage(), $ex->getTraceAsString());
            return $vehicle_booking;
        }
    }

    public function send_denied_access_socket($device, $message, $vehicle_num) {
        if ($device) {
            if ($device->available_device_id == 1) {
                $related_ticket_reader_id = $device->id;
            } elseif ($device->available_device_id == 3) {
                $related_ticket_reader = \App\DeviceTicketReaders::where([
                            ['device_id', $device->id]
                        ])->first();

                if (!$related_ticket_reader) {
                    return FALSE;
                }
                $related_ticket_reader_id = $related_ticket_reader->ticket_reader_id;
                $device = \App\LocationDevices::find($related_ticket_reader_id);
            } else {
                return FALSE;
            }

            $ip = $device->device_ip;
            $port = $device->device_port;
            $client = new \App\Http\Controllers\Connection\Client($ip, $port);

            $key = $this->location_created_at . '-' . $device->id;
            $command = 'Plate Reader Message';
            $data = '33|' . $key . '|' . $message;
            if ($vehicle_num) {
                $data .= "|tentave_vehicle_" . $vehicle_num;
            }
            $client->send($command, $data);
            return;
        }
    }

    public function user_arrival_notification($vehicle_booking, $status) {
        try {
            $type = NULL;
            $type_id = NULL;
            $customer_vehicle_info = \App\CustomerVehicleInfo::find($vehicle_booking->customer_vehicle_info_id);
            if (!$customer_vehicle_info) {
                return FALSE;
            }
            if ($customer_vehicle_info->userlist_user_id != NULL) {
                $type = 'user_list';
                $type_id = $customer_vehicle_info->userlist_user_id;
            } else {
                $type = 'customer';
                $type_id = $customer_vehicle_info->customer_id;
            }
            $vehicle = $customer_vehicle_info->num_plate;
            if ($vehicle_booking->vehicle_num) {
                $vehicle = $vehicle_booking->vehicle_num;
            }
            $in_out_notification = new \App\InOutNotification();
            if ($type) {
                $in_out_notification->type = $type;
            }
            if ($type_id) {
                $in_out_notification->type_id = $type_id;
            }
            $in_out_notification->type_id = $type_id;
            $in_out_notification->status = $status;
            $in_out_notification->vehicle_no = $vehicle;
            $in_out_notification->checkin_time = $vehicle_booking->checkin_time;
            $in_out_notification->checkout_time = $vehicle_booking->checkout_time;
            $in_out_notification->save();
            return TRUE;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    /**
     * Method for checking access based upon group or allowed vehicles per customer. This does not apply to whitelist users, send ticket and promo users
     * @param type $booking
     * @param type $device
     * @return type
     */
    public function check_access_right($booking, $device, $customer = null) {
        try {
            if ($customer != null) {
                if (!$customer->max_cars > 0) {
                    return array('allow_access' => true);
                }
                $booking_count = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                            $query->whereNull('check_out');
                        })->where('customer_id', $customer->id)->count();
                if (!($booking_count < $customer->max_cars)) {
                    $message = $this->ticket_reader->get_error_message('max_allowed_vehicles', $customer->max_cars, $this->lang_id);
                    return array('allow_access' => false, 'message' => $message);
                }
                return array('allow_access' => true);
            }
            if (!in_array($booking->type, array(3, 4))) {
                return array('allow_access' => true);
            }
            if ($booking->type == 4 && !isset($booking->customer_id)) {
                return array('allow_access' => true);
            }
            $device_group = $this->ticket_reader->device_has_group($device->id);
            if ($booking->type == 3) {
                $customer = $booking->customer;
                $access_right = null;
                $user_group = null;
                $access_right = null;
                $userlist_user = null;
                if ($customer) {
                    $userlist_user = $customer->user_list_users;
                    $user_group = $userlist_user->group;
                    $access_right = $customer->user_list_users->group_access;
                } else {
                    $user_vehicle = $booking->customer_vehicle_info;
                    $userlist_user = $user_vehicle->user_list_users;
                    $user_group = $userlist_user->group;
                    $access_right = $userlist_user->group_access;
                }
                if (!empty($userlist_user->language_id)) {
                    $this->lang_id = $userlist_user->language_id;
                }
                if (!$user_group && !$access_right) {
                    return array('allow_access' => true);
                }
                if ($access_right) {
                    if ($access_right->number_of_times) {
                        $vehicle_ids = $userlist_user->customer_vehicle_info()->pluck('id')->toArray();
                        $booking_count = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                                    $query->whereNotNull('check_out');
                                })->whereIn('customer_vehicle_info_id', $vehicle_ids)->count();
                        if (!($booking_count < $access_right->number_of_times)) {
                            $name = $this->ticket_reader->get_user_name($booking);
                            $message = $this->ticket_reader->get_error_message('access_number_of_time_expired', $name, $this->lang_id);
                            return array('allow_access' => false, 'message' => $message);
                        }
                    }
                    if (!empty($access_right->start_date) && !empty($access_right->end_date)) {
                        if (!(date('Y-m-d H:i:s') >= $access_right->start_date && date('Y-m-d H:i:s') <= $access_right->end_date)) {
                            $name = $this->ticket_reader->get_user_name($booking);
                            $message = $this->ticket_reader->get_error_message('access_expired', $name, $this->lang_id);
                            return array('allow_access' => false, 'message' => $message);
                        }
                    }
                    if ($access_right->allowed_no_of_vehicles) {
                        $vehicle_ids = $userlist_user->customer_vehicle_info()->pluck('id')->toArray();
                        $booking_count = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                                    $query->whereNull('check_out');
                                })->whereIn('customer_vehicle_info_id', $vehicle_ids)->where('customer_vehicle_info_id', '<>', $booking->customer_vehicle_info_id)->count();
                        if (!($booking_count < $access_right->allowed_no_of_vehicles)) {
                            $name = $this->ticket_reader->get_user_name($booking);
                            $message = $this->ticket_reader->get_error_message('access_vehicle_max', $name, $this->lang_id);
                            return array('allow_access' => false, 'message' => $message);
                        }
                    }
                    if (!$user_group) {
                        return array('allow_access' => true);
                    }
                }
                if ($device_group) {
                    $has_group_access = $this->ticket_reader->is_valid_group_device($user_group->id, $device->id);
                    if (!$has_group_access) {
                        $message = $this->ticket_reader->get_error_message('group_device_access_denied', '', $this->lang_id);
                        return array('allow_access' => false, 'message' => $message);
                    }
                    if ($device_group->group->has_anti_pass_back) {
                        //group_anti_passback
                        $booking_count = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                                    $query->whereNull('check_out');
                                })->where('customer_vehicle_info_id', $booking->customer_vehicle_info_id)->count();
                        if ($booking_count) {
                            $name = $this->ticket_reader->get_user_name($booking);
                            $message = $this->ticket_reader->get_error_message('group_anti_passback', $name, $this->lang_id);
                            return array('allow_access' => false, 'message' => $message);
                        }
                    }
                }
                if ($user_group->group_max) {
                    $userlist_group_users = \App\UserlistUsers::where('group_id', $user_group->id)->get();
                    $vehicle_ids = array();
                    foreach ($userlist_group_users as $user) {
                        $ids = $user->customer_vehicle_info()->pluck('id')->toArray();
                        $vehicle_ids = array_merge($vehicle_ids, $ids);
                    }
                    $booking_count = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                                $query->whereNull('check_out');
                            })->whereIn('customer_vehicle_info_id', $vehicle_ids)->where('customer_vehicle_info_id', '<>', $booking->customer_vehicle_info_id)->count();
                    if (!($booking_count < $user_group->group_max)) {
                        $name = $this->ticket_reader->get_user_name($booking);
                        $message = $this->ticket_reader->get_error_message('max_allowed_vehicles_group', $name, $this->lang_id);
                        return array('allow_access' => false, 'message' => $message);
                    }
                }
                return array('allow_access' => true);
            } else {
                $customer = $booking->customer;
                if (!$customer->max_cars > 0) {
                    return array('allow_access' => true);
                }
                $booking_count = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                            $query->whereNull('check_out');
                        })->where('customer_id', $customer->id)->where('customer_vehicle_info_id', '<>', $booking->customer_vehicle_info_id)->count();
                if (!($booking_count < $customer->max_cars)) {
                    $message = $this->ticket_reader->get_error_message('max_allowed_vehicles', $customer->max_cars, $this->lang_id);
                    return array('allow_access' => false, 'message' => $message);
                }
                return array('allow_access' => true);
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('check_group_access', $ex->getMessage(), $ex->getTraceAsString());
            return array('allow_access' => true);
        }
    }

}
