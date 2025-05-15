<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class DeviceSettings extends Controller {

    public function __construct() {
        
    }

    public function generate_device_settings(Request $request, $key, $id = null) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                return array(
                    'status' => 0,
                    'message' => 'Invalid Access ',
                    'data' => FALSE,
                );
            }
            $device_settings = array();
            $device_settings['id'] = $valid_settings->id;
            $device_settings['name'] = $valid_settings->device_name;
            $device_type = 'N/A';
            $available_device = \App\AvailableDevices::find($valid_settings->available_device_id);
            if ($available_device) {
                $device_type = $available_device->name;
            }
            $this->get_messages();
            $device_settings['type'] = $device_type;
            $device_settings['direction'] = $valid_settings->device_direction;
            $device_settings['ip'] = $valid_settings->device_ip;
            $device_settings['port'] = $valid_settings->device_port;
            $device_settings['anti_passback'] = $valid_settings->time_passback;
            $device_settings['barrier_close_time'] = $valid_settings->barrier_close_time;
            $device_settings['qr_code_type'] = $valid_settings->qr_code_type;
            $device_settings['enable_log'] = $valid_settings->enable_log == 0 ? 'no' : 'yes';
            $device_settings['enable_idle_screen'] = $valid_settings->enable_idle_screen == 0 ? 'no' : 'yes';
            $device_settings['focus_away'] = $valid_settings->focus_away == 0 ? 'no' : 'yes';
            $device_settings['opacity_input'] = $valid_settings->opacity_input == 0 ? 'no' : 'yes';
            $device_settings['camera_enabled'] = $valid_settings->camera_enabled == 0 ? 'no' : 'yes';
            $device_settings['od_enabled'] = $valid_settings->od_enabled == 0 ? 'no' : 'yes';
            $device_settings['has_sdl'] = $valid_settings->has_sdl == 0 ? 'no' : 'yes';
            $device_settings['has_pdl'] = $valid_settings->has_pdl == 0 ? 'no' : 'yes';
            $device_settings['disable_shopping_cart'] = 0;
            $device_settings['skip_start_screen'] = 0;
            $device_settings['advert_image_path'] = $valid_settings->advert_image_path;
            $device_settings['idle_screen_image'] = $valid_settings->idle_screen_image;
            $device_settings['change_screen_dimension'] = $valid_settings->change_screen_dimension;
            $device_settings['gate_close_transaction_enabled'] = $valid_settings->gate_close_transaction_enabled == 0 ? 'no' : 'yes';
            $device_settings['plate_correction_enabled'] = $valid_settings->plate_correction_enabled == 0 ? 'no' : 'yes';
            $device_settings['ods'] = $this->get_device_ods($valid_settings->id);
			if($valid_settings->available_device_id == 1){
				$device_settings['has_pr'] = count($this->get_device_ticket_readers($valid_settings->id)) > 0 ? 'yes' : 'no';
			}
			else if($valid_settings->available_device_id == 2){
                $device_settings['has_pr'] = "no";
            }
            $device_settings['ticket_readers'] = $this->get_device_ticket_readers($valid_settings->id);			
            $device_settings['messages'] = $this->get_messages();
            return array(
                'status' => 1,
                'message' => 'Success',
                'data' => $device_settings,
            );
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }

    public function get_device_details($id) {
        $device_details = \App\LocationDevices::find($id);
        if ($device_details) {
            return $device_details;
        }
        return FALSE;
    }

    public function device_settings_status(Request $request, $key, $id = null, $status) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                return array(
                    'status' => 0,
                    'message' => 'Invalid Access ',
                    'data' => FALSE,
                );
            }
            $error_message = 'Unable to connect';
            if (!$status) {
                if (!empty($request->error_message)) {
                    $error_message = $request->error_message;
                }
            }
            $device_id = $valid_settings->id;
            $device_settings = \App\DeviceSettings::where('device_id', $device_id)->first();
            if (!$device_settings) {
                $device_settings = new \App\DeviceSettings();
            }
            if ($status) {
                $device_settings->device_settings = 1;
                $device_settings->device_settings_details = NULL;
            } else {
                $device_settings->device_settings = 0;
                $device_settings->device_settings_details = $error_message;
            }

            $device_settings->save();
            return array(
                'status' => 1,
                'message' => 'Success',
                'data' => $device_settings,
            );
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'message' => 'Error',
                'data' => $ex->getMessage(),
            );
        }
    }

    public function other_settings_status(Request $request, $key, $id = null, $status) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                return array(
                    'status' => 0,
                    'message' => 'Invalid Access ',
                    'data' => FALSE,
                );
            }
            $error_message = 'Unable to connect';
            if (!$status) {
                if (!empty($request->error_message)) {
                    $error_message = $request->error_message;
                }
            }
            $device_id = $valid_settings->id;
            $device_settings = \App\DeviceSettings::where('device_id', $device_id)->first();
            if (!$device_settings) {
                $device_settings = new \App\DeviceSettings();
            }
            if ($status) {
                $device_settings->other_settings = 1;
                $device_settings->other_settings_details = NULL;
            } else {
                $device_settings->other_settings = 0;
                $device_settings->other_settings_details = $error_message;
            }

            $device_settings->save();
            return array(
                'status' => 1,
                'message' => 'Success',
                'data' => $device_settings,
            );
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'message' => 'Error',
                'data' => $ex->getMessage(),
            );
        }
    }

    public function get_messages() {
        $message_details = array();
        $msg = array();
        $msg_found = FALSE;
        $message_types = \App\MessageType::get();
        if ($message_types->count() > 0) {
            foreach ($message_types as $message_type) {
                $messages = \App\Messages::where('message_type_id', $message_type->id)->get();
                $msg = array();
                if ($messages->count() > 0) {
                    $msg_found = TRUE;
                    foreach ($messages as $message) {
                        $lang_details = \App\Language::find($message->language_id);
                        if (!$lang_details) {
                            continue;
                        }
                        $msg[] = (object) array(
                                    'code' => $lang_details->code,
                                    'message' => $message->message,
                        );
                    }
                }
                $message_details[] = (object) array(
                            'key' => $message_type->key,
                            'data' => $msg,
                );
            }
        }
        if ($msg_found) {
            return $message_details;
        }
        return FALSE;
    }

    public function get_device_ods($device_id) {
        $ods = array();
        $device_details = \App\LocationDevices::find($device_id);
        if (!$device_details) {
            return $ods;
        }
        if ($device_details->available_device_id == 1 || $device_details->available_device_id == 2) {
            $device_ods = \App\DeviceOds::where('device_id', $device_id)->get();
            if ($device_ods) {
                foreach ($device_ods as $device_od) {
                    $device = \App\LocationDevices::find($device_od->od_id);
                    if ($device) {
                        $ods[] = $device;
                    }
                }
            }
        }
        return $ods;
    }

    public function get_device_ticket_readers($device_id) {
        $ticket_readers = array();
        $device_details = \App\LocationDevices::find($device_id);
        if (!$device_details) {
            return $ticket_readers;
        }
        if ($device_details->available_device_id != 3 && $device_details->available_device_id != 1) {
            return $ticket_readers;
        }
        if($device_details->available_device_id == 1){
			$device_ticket_readers = \App\DeviceTicketReaders::where('ticket_reader_id', $device_id)->first();
			if ($device_ticket_readers) {
				$ticket_readers[] = $device_details;
			}
			return $ticket_readers;
		}
        $device_ticket_readers = \App\DeviceTicketReaders::where('device_id', $device_id)->first();
        if ($device_ticket_readers) {
            $device = \App\LocationDevices::find($device_ticket_readers->ticket_reader_id);
            if ($device) {
                $ticket_readers[] = $device;
            }
        }
        return $ticket_readers;
    }

    public function get_plate_reader_ods($device_id) {
        $ticket_readers = array();
        $device_details = \App\LocationDevices::find($device_id);
        if (!$device_details) {
            return $ticket_readers;
        }
        if ($device_details->available_device_id != 3) {
            return $ticket_readers;
        }
        $device_ticket_readers = \App\DeviceTicketReaders::where('device_id', $device_id)->first();
        if ($device_ticket_readers) {
            $ticket_readers[] = $this->get_device_ods($device_ticket_readers->ticket_reader_id);
        }
        return $ticket_readers;
    }
    
    public function get_device_messages(Request $request, $key, $id) {
        $settings = new Settings();
        $valid_settings = $settings->is_valid_call($key, $id);
        if (!$valid_settings) {
            return array(
                'status' => 0,
                'message' => 'Invalid Access ',
                'data' => FALSE,
            );
        }
        return array(
            'status' => 1,
            'message' => 'Success',
            'data' => $this->get_messages(),
        );
    }

}
