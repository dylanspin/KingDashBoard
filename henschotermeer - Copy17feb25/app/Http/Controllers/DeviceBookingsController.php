<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\DeviceBookings;
use File;
use Zip;
use App\DeviceLog;
class DeviceBookingsController extends Controller {

    public function getIndex(Request $request) {
        $search_device = '';
        $search_type = '';
        $search_val = '';
        $onLineBookings = array();
        $plate_readers = \App\LocationDevices::where('available_device_id', 3)
                ->get();
        $bookings = DeviceBookings::sortable();
        if ((isset($request->search_device) && !empty($request->search_device)) || (isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))) {
            if (isset($request->search_btn) || isset($request->download_btn)) {
                $search_device = $request->search_device;
                $search_type = $request->search_type;
                $search_val = $request->search_val;
                if (!empty($request->search_device)) {
                    $bookings->where('device_id', $request->search_device);
                }
                if (!empty($request->search_type)) {
                    if ($request->search_type == 'low') {
                        $bookings = $bookings->where('confidence', '<', 80);
                    } elseif ($request->search_type == 'high') {
                        $bookings = $bookings->where('confidence', '>=', 80);
                    }
                }
                if (!empty($request->search_val)) {
                    $bookings->where('vehicle_num', 'LIKE', "%{$request->search_val}%");
                }
            }
        }
        $bookings = $bookings->orderBy('created_at', 'desc');
        if (isset($request->download_btn)) {
            $bookings = $bookings->get();
            $zip_file = 'device_bookings.zip';
            $zip = new \ZipArchive();
            if ($zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                foreach ($bookings as $booking) {
                    if ($booking->file_path && File::exists(public_path($booking->file_path))) {
                        $file_path = explode('/', $booking->file_path);
                        $zip->addFile(public_path($booking->file_path), $file_path[3]);
                    }
                }
                $zip->close();
            }
            return response()->download($zip_file);
        }
        $bookings = $bookings->paginate(25);
        return view('device-bookings.index', compact('bookings', 'search_type', 'search_val', 'search_device', 'plate_readers'));
    }

    public function downloadImage(Request $request, $id) {
        $device_booking = DeviceBookings::find($id);
        if ($device_booking) {
            if (File::exists(public_path($device_booking->file_path))) {
                return response()->download(public_path($device_booking->file_path));
            }
        }
        Session::flash('heading', 'Error!');
        Session::flash('message', 'Image not Found!');
        Session::flash('icon', 'error');
        return redirect()->back()->withInput();
    }
	public function get_access_details($id, Request $request)
    {
        $device_logs = DeviceLog::whereNull('parent_id')->where('device_booking_id', $id)
            ->orderBy('created_at', 'desc')->paginate(200);
        $from_date = null;
        $to_date = null;
        $type = null;
        $message = null;

        if ($request->search_btn) {

            if (!empty($request->type) && !empty($request->message) && !empty($request->datefilter)) {
                $message = $request->message;
                $type = $request->type;
                $date = explode(' - ', $request->datefilter);
                $filter_valid_dates_array[0] = str_replace("/", "-", $date[0]);
                $filter_valid_dates_array[1] = str_replace("/", "-", $date[1]);
                $from_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[0]));
                $to_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[1]));
                $device_logs = DeviceLog::whereBetween('created_at', array($from_date, $to_date))
                    ->where('type', $request->type)->where('message', 'LIKE', "%{$request->message}%")
                    ->where('device_booking_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            } else if (empty($request->type) && !empty($request->message) && !empty($request->datefilter)) {
                $message = $request->message;
                $date = explode(' - ', $request->datefilter);
                $filter_valid_dates_array[0] = str_replace("/", "-", $date[0]);
                $filter_valid_dates_array[1] = str_replace("/", "-", $date[1]);
                $from_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[0]));
                $to_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[1]));

                $device_logs = DeviceLog::whereBetween('created_at', array($from_date, $to_date))
                    ->where('message', 'LIKE', "%{$request->message}%")
                    ->where('device_booking_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            } else if (!empty($request->type) && empty($request->message) && !empty($request->datefilter)) {
                $type = $request->type;
                $date = explode(' - ', $request->datefilter);
                $filter_valid_dates_array[0] = str_replace("/", "-", $date[0]);
                $filter_valid_dates_array[1] = str_replace("/", "-", $date[1]);
                $from_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[0]));
                $to_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[1]));

                $device_logs = DeviceLog::whereBetween('created_at', array($from_date, $to_date))
                    ->where('type', $request->type)
                    ->where('device_booking_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            } else if (!empty($request->type) && !empty($request->message) && empty($request->datefilter)) {
                $message = $request->message;
                $type = $request->type;
                $device_logs = DeviceLog::where('type', $request->type)->where('message', 'LIKE', "%{$request->message}%")
                ->where('device_booking_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            } else if (!empty($request->datefilter) && empty($request->type) && empty($request->message)) {

                $date = explode(' - ', $request->datefilter);
                $filter_valid_dates_array[0] = str_replace("/", "-", $date[0]);
                $filter_valid_dates_array[1] = str_replace("/", "-", $date[1]);
                $from_date = date('Y-m-d H:i:s', strtotime($filter_valid_dates_array[0]));
                $to_date = date('Y-m-d H:i:s', strtotime($filter_valid_dates_array[1]));

                $device_logs = DeviceLog::whereBetween('created_at', array($from_date, $to_date))
                    ->where('device_booking_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            } else if (!empty($request->type) && empty($request->message) && empty($request->datefilter)) {
                $type = $request->type;
                $device_logs = DeviceLog::where('type', $request->type)
                    ->where('device_booking_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            } else if (!empty($request->message) && empty($request->type) && empty($request->datefilter)) {
                $message = $request->message;
                $device_logs = DeviceLog::where('message', 'LIKE', "%{$request->message}%")
                ->where('device_booking_id', $id)
                    ->orderBy('created_at', 'desc')->paginate(200);
            }
        }

        return view('device-bookings.access_detail', [
            'device_logs' => $device_logs,
            'id' => $id,
            'type' => $type,
            'message' => $message,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'datefilter' => $request->datefilter,
        ]);
    }

    public function get_device_access_details($id, Request $request)
    {

        $device_logs = DeviceLog::whereNull('parent_id')->where('device_id', $id)
            ->orderBy('created_at', 'desc')->paginate(200);
        $from_date = null;
        $to_date = null;
        $type = null;
        $message = null;

        if ($request->search_btn) {

            if (!empty($request->type) && !empty($request->message) && !empty($request->datefilter)) {
                $message = $request->message;
                $type = $request->type;
                $date = explode(' - ', $request->datefilter);
                $filter_valid_dates_array[0] = str_replace("/", "-", $date[0]);
                $filter_valid_dates_array[1] = str_replace("/", "-", $date[1]);
                $from_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[0]));
                $to_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[1]));
                $device_logs = DeviceLog::whereBetween('created_at', array($from_date, $to_date))
                    ->where('type', $request->type)->where('message', 'LIKE', "%{$request->message}%")
                    ->where('device_id', $id)

                    ->orderBy('created_at', 'desc')
                    ->paginate(200);
            } else if (empty($request->type) && !empty($request->message) && !empty($request->datefilter)) {
                $message = $request->message;
                $date = explode(' - ', $request->datefilter);
                $filter_valid_dates_array[0] = str_replace("/", "-", $date[0]);
                $filter_valid_dates_array[1] = str_replace("/", "-", $date[1]);
                $from_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[0]));
                $to_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[1]));

                $device_logs = DeviceLog::whereBetween('created_at', array($from_date, $to_date))
                    ->where('message', 'LIKE', "%{$request->message}%")
                    ->where('device_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            } else if (!empty($request->type) && empty($request->message) && !empty($request->datefilter)) {
                $type = $request->type;
                $date = explode(' - ', $request->datefilter);
                $filter_valid_dates_array[0] = str_replace("/", "-", $date[0]);
                $filter_valid_dates_array[1] = str_replace("/", "-", $date[1]);
                $from_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[0]));
                $to_date = date('Y-m-d H:i', strtotime($filter_valid_dates_array[1]));

                $device_logs = DeviceLog::whereBetween('created_at', array($from_date, $to_date))
                    ->where('type', $request->type)
                    ->where('device_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            } else if (!empty($request->type) && !empty($request->message) && empty($request->datefilter)) {
                $message = $request->message;
                $type = $request->type;
                $device_logs = DeviceLog::where('type', $request->type)->where('message', 'LIKE', "%{$request->message}%")
                ->where('device_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            } else if (!empty($request->datefilter) && empty($request->type) && empty($request->message)) {

                $date = explode(' - ', $request->datefilter);
                $filter_valid_dates_array[0] = str_replace("/", "-", $date[0]);
                $filter_valid_dates_array[1] = str_replace("/", "-", $date[1]);
                $from_date = date('Y-m-d H:i:s', strtotime($filter_valid_dates_array[0]));
                $to_date = date('Y-m-d H:i:s', strtotime($filter_valid_dates_array[1]));

                $device_logs = DeviceLog::whereBetween('created_at', array($from_date, $to_date))
                    ->where('device_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            } else if (!empty($request->type) && empty($request->message) && empty($request->datefilter)) {
                $type = $request->type;
                $device_logs = DeviceLog::where('type', $request->type)
                    ->where('device_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            } else if (!empty($request->message) && empty($request->type) && empty($request->datefilter)) {
                $message = $request->message;
                $device_logs = DeviceLog::where('message', 'LIKE', "%{$request->message}%")
                ->where('device_id', $id)

                    ->orderBy('created_at', 'desc')->paginate(200);
            }
        }

        return view('device-bookings.device_access_detail', [
            'device_logs' => $device_logs,
            'id' => $id,
            'type' => $type,
            'message' => $message,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'datefilter' => $request->datefilter,
        ]);
    }

}
