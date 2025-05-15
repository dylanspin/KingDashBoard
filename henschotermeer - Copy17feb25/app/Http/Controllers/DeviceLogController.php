<?php

namespace App\Http\Controllers;

use App\Bookings;
use Illuminate\Http\Request;
use App\Http\Controllers\Settings\Settings;
use App\Http\Controllers\Settings\VerifyBookings;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PlateReaderController\VerifyVehicle;
use App\LocationOptions;
use App\DeviceLog;
use App\DeviceDownloadLog;
use App\DeviceBookings;
use Exception;


class DeviceLogController extends Controller
{
    //
    public $confidence_val = 80;
    public $lang_id = FALSE;
    public $location_created_at = '1552661741';
    public $ticket_reader;
    public $lag_time = 30;
    public $settings;
    public $url = "";
    public $location_setting = FALSE;
    public $verifyVehicle;
    public function __construct($key = NULL)
    {
        $this->url = env('API_BASE_URL');
        $this->ticket_reader = new VerifyBookings();
        $this->settings = new Settings();
        $this->verifyVehicle = new VerifyVehicle();
    }
    public function store_temporary_booking(Request $request, $key, $id)
    {

        try {
            $settings = new Settings();
            $valid_settings = $this->verifyVehicle->is_valid_call($key, $id);
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
            $temporary_booking = new DeviceBookings();
            $temporary_booking->device_id = $id;
            $temporary_booking->save();
            $message = "temporary booking created";
            return array(
                'status' => 1,
                'access_status' => 'success',
                'message' => $message,
                // 'od_sent' => $settings->send_message_od($id, $message, 'welcome_entrance'),
                'temporary_booking_id' => $temporary_booking->id
            );
        } catch (Exception $ex) {
            $error_log = new LogController();
            $error_log->log_create('device_temporary_booking', $ex->getMessage(), $ex->getTraceAsString());
            return $error_log;
        }
    }
    public function store_device_logs(Request $request, $key, $id)
    {
        try {
            $valid_settings = $this->verifyVehicle->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->ticket_reader->getMessage('unknown', '', $this->lang_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'od_sent' => $this->settings->send_message_od($id, $message, 'unknown'),
                    'data' => FALSE,
                );
            }

            $curTime = microtime(true);
            $file_path = NULL;
            $data = $request->all();
            if (array_key_exists('device_booking_id', $data)) {
                $get_temporary_booking = DeviceBookings::where('id', $data['device_booking_id'])->first();
                if ($request->hasfile('file')) {
                    foreach ($request->file('file') as $index => $file) {
                        $device_log = new DeviceLog();
                        $extension = $file->extension() ?: 'png';
                        $destinationPath = public_path('/uploads/vehicles');
                        $safeName = str_random(10) . '.' . $extension;
                        $file->move($destinationPath, $safeName);
                        $request['pic'] = $safeName;
                        $file_path = '/uploads/vehicles' . '/' . $safeName;

                        $device_log->device_booking_id = $get_temporary_booking->id;
                        if (array_key_exists("device_id", $data)) {
                            $device_log->device_id = $data['device_id'];
                        }
                        if (array_key_exists("vehicle_number", $data)) {
                            $device_log->vehicle_number = $data['vehicle_number'];
                        }
                        $device_log->type = strtolower($data['type']);
                        $device_log->message = $data['message'];
                        $device_log->file_path = $file_path;
                        $device_log->image_index = $index;
                        if ($index > 0) {
                            $recent_log = DeviceLog::where('device_booking_id', $data['device_booking_id'])->latest()->first();
                            $device_log->parent_id = $recent_log->id;
                        }
                        $device_log->save();
                    }
                    $message = 'device log against this booking saved successfully';
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'od_sent' => $this->settings->send_message_od($id, $message, 'welcome_entrance'),
                        'data' => FALSE,
                    );
                } else {
                    $device_log = new DeviceLog();
                    $device_log->device_booking_id = $data['device_booking_id'];
                    if (array_key_exists("device_id", $data)) {
                        $device_log->device_id = $data['device_id'];
                    }
                    if (array_key_exists("vehicle_number", $data)) {
                        $device_log->vehicle_number = $data['vehicle_number'];
                    }
                    $device_log->type = strtolower($data['type']);
                    $device_log->message = $data['message'];
                    $device_log->save();
                    $message = 'device log against this booking saved successfully';
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'od_sent' => $this->settings->send_message_od($id, $message, 'welcome_entrance'),
                        'data' => FALSE,
                    );
                }
            } else {
                $deviceBooking = false;
                if ($request->hasfile('file')) {
                    foreach ($request->file('file') as $index => $file) {
                        $device_log = new DeviceLog();
                        $extension = $file->extension() ?: 'png';
                        $destinationPath = public_path('/uploads/vehicles');
                        $safeName = str_random(10) . '.' . $extension;
                        $file->move($destinationPath, $safeName);
                        $request['pic'] = $safeName;
                        $file_path = '/uploads/vehicles' . '/' . $safeName;
                        if (array_key_exists('vehicle_number', $data)) {
                            $deviceBooking = $this->findAndSetBooking($data['vehicle_number'], $valid_settings->id);
                        }
                        $device_log->device_booking_id = $deviceBooking->id;
                        if (array_key_exists("device_id", $data)) {
                            $device_log->device_id = $data['device_id'];
                        }
                        $device_log->type = strtolower($data['type']);
                        $device_log->message = $data['message'];
                        $device_log->file_path = $file_path;
                        $device_log->image_index = $index;
                        if ($index > 0) {
                            $recent_log = DeviceLog::where('device_booking_id', $deviceBooking->id)->latest()->first();
                            $device_log->parent_id = $recent_log->id;
                        }
                        $device_log->save();
                    }
                    $message = 'device log against this booking saved successfully';
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'od_sent' => $this->settings->send_message_od($id, $message, 'welcome_entrance'),
                        'data' => FALSE,
                    );
                } else {
                    $device_log = new DeviceLog();
                    if (array_key_exists("device_id", $data)) {
                        $device_log->device_id = $data['device_id'];
                    } else {
                        $device_log->device_id = $valid_settings->id;
                    }
                    if (array_key_exists("vehicle_number", $data)) {
                        $device_log->vehicle_number = $data['vehicle_number'];
                    }
                    $device_log->type = strtolower($data['type']);
                    $device_log->message = $data['message'];
                    $device_log->save();
                }
                $message = "Logs save against device";
                return array(
                    'status' => 1,
                    'access_status' => 'success',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
        } catch (Exception $ex) {
            $error_log = new LogController();
            $error_log->log_create('store_device_log', $ex->getMessage(), $ex->getTraceAsString());
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'message' => $ex->getMessage(),
                // 'od_sent' => $settings->send_message_od($id, $message, 'unknown'),
                'data' => FALSE,
            );
        }
    }
    public function upload_log_file(Request $request, $key, $id)
    {
        try {
            $valid_settings = $this->verifyVehicle->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->ticket_reader->getMessage('unknown', '', $this->lang_id);
                return array(
                    'status' => 1,
                    'message' => $message,
                    'od_sent' => $this->settings->send_message_od($id, $message, 'unknown'),
                    'data' => FALSE,
                );
            }
            $file_path = '';
            if ($request->hasfile('file')) {
                $file = $request->file('file');
                $extension = $file->extension();
                $destinationPath = public_path('/device-logs');
                $safeName = str_random(10) . '.' . $extension;
                $file->move($destinationPath, $safeName);
                $file_path = '/device-logs' . '/' . $safeName;
                $download_log = new DeviceDownloadLog();
                $download_log->device_id = $valid_settings->id;
                $download_log->message = 'file found';
                $download_log->file_path = $file_path;
                $download_log->save();
                $message = 'log file downloaded successfully';
                return array(
                    'status' => 1,
                    'message' => $message,
                    'data' => FALSE,
                );
            } else {
                $download_log = new DeviceDownloadLog();
                $download_log->device_id = $valid_settings->id;
                $download_log->message = 'file not found';
                $download_log->save();
                $message = 'log file not found';
                return array(
                    'status' => 0,
                    'message' => $message,
                    'data' => FALSE,
                );
            }
        } catch (Exception $ex) {
            return array(
                'status' => 0,
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }
    public function findAndSetBooking($vehicleNumber, $deviceId)
    {
        try {
            $booking = Bookings::where('vehicle_num', $vehicleNumber)->orderBy('created_at', 'desc')->first();
            if (empty($booking)) {
                return false;
            }
            $deviceBooking = DeviceBookings::where('device_id', $deviceId)->where('vehicle_num', $booking->vehicle_num)->orderBy('created_at', 'desc')->first();
            if (!$deviceBooking) {
                return false;
            }
            $deviceBooking->booking_id = $booking->id;
            $deviceBooking->update();
            return $deviceBooking;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
