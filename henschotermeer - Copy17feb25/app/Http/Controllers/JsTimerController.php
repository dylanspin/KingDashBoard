<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use App\Http\Controllers\DashboardController;

class JsTimerController extends Controller
{

    public $controller = 'App\Http\Controllers\JsTimerController';
    public $dashboard_controller;

    public function __construct()
    {
        $this->dashboard_controller = new DashboardController();
    }

    /**
     * ajax based changes of dashboard
     * @param Request $request
     * @return type
     */
    public function check_dashboard_changes_timer(Request $request)
    {
        $response = array();
        try {
            $response['door_changes'] = $this->check_door_changes();
            $response['widgets'] = $this->get_widgets_details();
            $response['vehicles_on_location_con'] = $this->get_at_location_vehicle_details();
            $response['transaction_on_location_con'] = $this->get_at_location_transaction_details();
            return \Illuminate\Support\Facades\Response::json($response);
        } catch (\Exception $ex) {
            echo $ex->getLine();
            exit;
        }
    }

    /**
     * ajax based changes of person dashboard
     * @param Request $request
     * @return type
     */
    public function check_person_dashboard_changes_timer(Request $request)
    {
        $response = array();
        try {
            $response['door_changes'] = $this->check_door_changes();
            $response['widgets'] = $this->get_widgets_details(TRUE);
            $response['persons_on_location_con'] = $this->get_at_location_person_details();
            $response['transaction_on_location_con'] = $this->get_at_location_transaction_details(TRUE);
            return \Illuminate\Support\Facades\Response::json($response);
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            exit;
        }
    }

    /**
     * ajax based changes of dashboard Check door changes 
     * @return type
     */
    public function check_door_changes()
    {
        $devices_opened = \App\LocationDevices::whereIn('available_device_id', [1, 2])->get();
        if ($devices_opened->count() > 0) {
            foreach ($devices_opened as $device) {
                $response[$device->id] = array(
                    'is_opened' => $device->is_opened,
                    'direction' => $device->device_direction,
                    'barrier_status' => $device->barrier_status,
                    'has_always_access' => $device->has_always_access
                );
            }
        }
        return $response;
    }

    /**
     * ajax based changes of dashboard Check device status changes
     * @return type
     */
    public function check_changes_timer()
    {
        $response = array();
        $response['devices'] = $this->check_device_status();
        return \Illuminate\Support\Facades\Response::json($response);
    }

    /**
     * ajax based changes of dashboard Devices status
     * @return type
     */
    public function check_device_status()
    {
        $devices_updated = \App\LocationDevices::get();
        if ($devices_updated->count() > 0) {
            foreach ($devices_updated as $device) {
                $response[$device->id] = $device->is_synched;
            }
        }
        return $response;
    }

    /**
     * ajax based changes of dashboard Stucked and non stucked plate readers
     * @param Request $request
     * @return type
     */
    public function manage_stucked_plate_readers(Request $request)
    {
        $non_stucked = array();
        $stucked = array();
        $devices_updated = \App\LocationDevices::get();
        if ($devices_updated->count() > 0) {
            foreach ($devices_updated as $device) {
                if (!$device->is_synched) {
                    continue;
                }
                $device_confidence_level = $device->confidence;
                $lagTime = strtotime('-2 minutes');
                $updated_time = date('Y-m-d H:i:s', $lagTime);
                //                $device_latest_entry = \App\DeviceBookings::where('device_id', $device->id)
                //                        ->orderby('created_at', 'DESC')
                //                        ->first();
                $device_latest_entry = \App\DeviceBookings::where('device_id', $device->id)->where('created_at', '>', $updated_time)
                    ->orderby('created_at', 'DESC')
                    ->first();

                if (!$device_latest_entry) {
                    $non_stucked[$device->id] = $device->id;
                    continue;
                }
                if ($device_confidence_level <= $device_latest_entry->confidence) {
                    $non_stucked[$device->id] = $device->id;
                    continue;
                }
                $non_stucked[$device->id] = $device->id;
                // $stucked[$device->id] = array(
                //     'id' => $device_latest_entry->id,
                //     'confidence' => $device_latest_entry->confidence,
                //     'file_path' => $device_latest_entry->file_path,
                //     'vehicle_num' => $device_latest_entry->vehicle_num,
                // );
            }
        }
        return array(
            'stucked' => $stucked,
            'non_stucked' => $non_stucked,
        );
    }

    /**
     * ajax based changes of dashboard widget details
     * @return typ
     */
    public function get_widgets_details($das_p = FALSE)
    {
        $widget1 = $this->dashboard_controller->get_widget1_details($das_p);
        $widget2 = $this->dashboard_controller->get_widget2_details($das_p);
        $widget3 = $this->dashboard_controller->get_widget3_details($das_p);
        if ($das_p) {
            $widget1_con = "<h4 class=\"color-white\">" . $widget1->expected_bookings . "</h4>";
            $widget1_con .= "<h4 class=\"color-white\">" . $widget1->arrival_d . "</h4>";
            $widget1_con .= "<h4 class=\"color-white\">" . $widget1->arrival_left . "</h4>";
            $widget2_con = "<h4 class=\"color-white\">" . $widget2->on_location . "</h4>";
            $widget2_con .= "<h4 class=\"color-white\">" . $widget2->total . "</h4>";
            $widget2_con .= "<h4 class=\"color-white\">" . $widget2->booking_left . "</h4>";
        } else {
            //            $widget1_con = "<h4 class=\"color-white\">" . $widget1->total_spots . "</h4>";
            $widget1_con = "<h4 class=\"color-white\">" . $widget1->total_bookings . "</h4>";
            $widget1_con .= "<h4 class=\"color-white\">" . $widget1->arrival_d . "</h4>";
            $widget1_con .= "<h4 class=\"color-white\">" . $widget1->arrival_left . "</h4>";
            //            $widget1_con .= "<h4 class=\"color-white\">" . $widget1->unexpected_bookings . "</h4>";
            //            $widget2_con = "<h4 class=\"color-white\">" . $widget2->total . "</h4>";
            //            $widget2_con .= "<h4 class=\"color-white\">" . $widget2->expected . "</h4>";
            $widget2_con = "<h4 class=\"color-white\">" . $widget2->on_location . "</h4>";
            $widget2_con .= "<h4 class=\"color-white\">" . $widget2->arrivals . "</h4>";
            $widget2_con .= "<h4 class=\"color-white\">" . $widget2->booking_left . "</h4>";
        }
        if ($das_p) {
            if (count($widget3->device_alerts) > 0) {
                $widget3_con = "<a href=\"" . url('details/widget3') . "\">";
                foreach ($widget3->device_alerts as $data) {
                    $device_name = strtoupper($data->location_devices ? $data->location_devices : 'N/A');
                    $device_message = $data->message ? $data->message : 'N/A';
                    $status = $data->status == 0 ? 'color-red' : 'color-yellow';
                    $widget3_con .= "<div class=\"col-md-6\">";
                    $widget3_con .= "<h4 class=\"" . $status . " wrap-word-custom\" data-toggle=\"tooltip\" title=\"" . $device_name . "\" style=\"margin:6px 0;\">" . $device_name . "</h4>";
                    $widget3_con .= "</div>";
                    $widget3_con .= "<div class=\"col-md-6\">";
                    $widget3_con .= "<h4 class=\"" . $status . " wrap-word-custom\" data-toggle=\"tooltip\" title=\"" . $device_message . "\" style=\"margin:6px 0;\">" . $device_message . "</h4>";
                    $widget3_con .= "</div>";
                }
            } else {
                if ($das_p) {
                    $widget3_con = "<h4 class=\"color-white\">" . $widget1->total_spots . "</h4>";
                    $widget3_con .= "<h4 class=\"color-white\">" . $widget1->available_spots . "</h4>";
                    $widget3_con .= "<h4 class=\"color-white\">" . $widget1->online_person_spot . "</h4>";
                }
                //            else{
                //                $widget3_con = "<div class=\"col-md-12 text-center pt-10 pr-0\">";
                //                $widget3_con .= "<i class=\"fa fa-check f-78\"></i>";
                //                $widget3_con .= "<h3 class=\"color-white\">No Alerts</h3>";
                //            }
            }
        } else {
            $widget3_con = "<h4 class=\"color-white\">" . $widget1->total_spots . "</h4>";
            $widget3_con .= "<h4 class=\"color-white\">" . $widget1->available_spots . "</h4>";
            $widget3_con .= "<h4 class=\"color-white\">" . $widget1->online_parking_spot . "</h4>";
        }
        return array(
            'widget1_con' => $widget1_con,
            'widget2_con' => $widget2_con,
            'widget3_con' => $widget3_con
        );
    }

    /**
     * ajax based changes of dashboard Devices section
     * @return type
     */
    public function get_devices_details()
    {
        $devices = $this->dashboard_controller->get_devices();
        $devices_con = "";
        if (count($devices) > 0) {
            if (count($devices['ticket_readers']) > 0) {
                foreach ($devices['ticket_readers'] as $ticket_reader_device) {
                    $devices_con .= "<div class=\"col-md-4 col-sm-12 mb-20 device_item device_" . $ticket_reader_device['id'] . "\">";
                    $divces_con .= "<div class=\"bg-primary color-white col-md-12 col-xs-12 pb-15 pl-0 pr-0\">";
                    $divces_con .= "<div class=\"col-md-12 col-xs-12 pl-0 pr-0 pt-5 pb-5 header-device-section\">";
                    $divces_con .= "<div class=\"col-sm-6\">";
                    $divces_con .= "<h6 class=\"box-title text-left color-white wrap-word-custom f-17\" data-toggle=\"tooltip\" title=\"" . strtoupper($ticket_reader_device['name']) . "\">" . strtoupper($ticket_reader_device['name']) . "</h6>";
                    $divces_con .= "</div>";
                    $divces_con .= "<div class=\"col-sm-6\">";
                    $divces_con .= "<h6 class=\"box-title text-right cursor-pointer color-white f-17 wrap-word-custom\">" . __('dashboard.transaction') . " <i class=\"fa fa-chevron-down show_transactions\"></i><i class=\"fa fa-chevron-up hide_transactions hidden\"></i></h6>";
                    $divces_con .= "</div>";
                    $divces_con .= "</div>";
                    $divces_con .= "<div class=\"transactions_con hidden\">";
                    if (count($ticket_reader_device['transactions']) > 0) {
                        foreach ($ticket_reader_device['transactions'] as $ticket_reader_device_transactions) {
                            $divces_con .= "<div class=\"col-md-12 col-xs-12\">";
                            $divces_con .= "<a style=\"color:white!important\" target=\"_blank\" href=\"" . url('/transaction/1/' . $ticket_reader_device_transactions['id']) . "\">" . $ticket_reader_device_transactions['content'] . "</a>";
                            $divces_con .= "</div>";
                        }
                    } else {
                        $divces_con .= "<div class=\"col-md-12 col-xs-12\">";
                        $divces_con .= "<p>No Transactions</p>";
                        $divces_con .= "</div>";
                    }
                    $divces_con .= "</div>";
                    $divces_con .= "<div class=\"other_content\">";
                    $divces_con .= "<div class=\"col-md-6 gates h-100\">";
                    if ($ticket_reader_device['is_opened']) {
                        $divces_con .= "<div class=\"barier-closed barier-closed-" . $ticket_reader_device['id'] . " text-center pt-20 hidden\" data-device_id=\"" . $ticket_reader_device['id'] . "\">";
                        $divces_con .= "<img class=\"filter\" width=\"100\"  src=\"" . asset('plugins/images/icons/b.png') . "\" alt=\"Transaction\">";
                        $divces_con .= "</div>";
                        $divces_con .= "<div class=\"barier-closed barier-closed-" . $ticket_reader_device['id'] . " text-center\" data-device_id=\"" . $ticket_reader_device['id'] . "\">";
                        $divces_con .= "<img class=\"filter\" width=\"100\"  src=\"" . asset('plugins/images/icons/bo.png') . "\" alt=\"Transaction\">";
                        $divces_con .= "</div>";
                    } else {
                        $divces_con .= "<div class=\"barier-closed barier-closed-" . $ticket_reader_device['id'] . " text-center pt-20\" data-device_id=\"" . $ticket_reader_device['id'] . "\">";
                        $divces_con .= "<img class=\"filter\" width=\"100\"  src=\"" . asset('plugins/images/icons/b.png') . "\" alt=\"Transaction\">";
                        $divces_con .= "</div>";
                        $divces_con .= "<div class=\"barier-closed barier-closed-" . $ticket_reader_device['id'] . " text-center hidden\" data-device_id=\"" . $ticket_reader_device['id'] . "\">";
                        $divces_con .= "<img class=\"filter\" width=\"100\"  src=\"" . asset('plugins/images/icons/bo.png') . "\" alt=\"Transaction\">";
                        $divces_con .= "</div>";
                    }
                    $divces_con .= "";
                    $divces_con .= "";
                }
            }
        } else {
            $devices_con .= "<p>No Device Added yet</p>";
        }
        return array(
            'devices_con' => $devices_con
        );
    }

    /**
     * ajax based changes of dashboard At location vehicle
     * @return type
     */
    public function get_at_location_vehicle_details()
    {
        $at_location_vehicle = $this->dashboard_controller->at_location_vehicles(5);
        $at_location_con = "<div class = \"row\">";
        $at_location_con .= "<div class = \"col-sm-12\">";
        $at_location_con .= "<h4 class = \"box-title\">" . __('dashboard.vehicles_on_loc') . "</h4>";
        $at_location_con .= "</div>";
        $at_location_con .= "</div>";
        $at_location_con .= "<div class = \"table-responsive row\">";
        $at_location_con .= "<table class = \"table color-table info-table\">";
        $at_location_con .= "<thead>";
        $at_location_con .= "<tr>";
        $at_location_con .= "<th>#</th>";
        $at_location_con .= "<th>" . __('dashboard.name') . "</th>";
        $at_location_con .= "<th>" . __('dashboard.vehicle') . "</th>";
        $at_location_con .= "<th>" . __('dashboard.check_in') . "</th>";
        $at_location_con .= "<th>" . __('dashboard.confidence') . "</th>";
        $at_location_con .= "</tr>";
        $at_location_con .= "</thead>";
        $at_location_con .= "<tbody>";
        foreach ($at_location_vehicle as $key => $bookings) {

            $count = $key + 1;
            $at_location_con .= "<tr>";
            $at_location_con .= "<td>" . $count . "</td>";
            $at_location_con .= "<td><a href = \"" . url('/booking/' . $bookings->id) . "\" class=\"text-link\">" . $bookings->name . "</a></td>";
            if ($bookings->low_confidence) {
                $at_location_con .= "<td class = \"cursor-pointer text-link\" onclick=\"edit_vehicle_num(" . $bookings->id . ")\">" . $bookings->vehicle_num . "</td>";
            } else {
                $at_location_con .= "<td>" . $bookings->vehicle_num . "</td>";
            }
            $at_location_con .= "<td>" . $bookings->checkin . "</td>";
            $at_location_con .= "<td>" . $bookings->confidence . "</td>";
            $at_location_con .= "</tr>";
        }
        $at_location_con .= "</tbody>";
        $at_location_con .= "</table>";
        $at_location_con .= "</div>";
        if (count($at_location_vehicle) > 0) {
            $at_location_con .= "<div class = \"row\">";
            $at_location_con .= "<div class = \"col-sm-12 text-center\">";
            $at_location_con .= "<a href=\"" . url('/currently_on_location') . "\" class=\"btn btn-primary\">" . __('dashboard.view_more') . "</a>";
            $at_location_con .= "</div>";
            $at_location_con .= "</div>";
        }
        return array(
            'at_location_con' => $at_location_con
        );
    }

    /**
     * ajax based changes of dashboard at location persons
     * @return type
     */
    public function get_at_location_person_details()
    {
        $at_location_person = $this->dashboard_controller->at_location_persons();
        $at_location_con = "<div class = \"row\">";
        $at_location_con .= "<div class = \"col-sm-12\">";
        $at_location_con .= "<h4 class = \"box-title\">" . __('dashboard.persons_on_loc') . "</h4>";
        $at_location_con .= "</div>";
        $at_location_con .= "</div>";
        $at_location_con .= "<div class = \"table-responsive row\">";
        $at_location_con .= "<table class = \"table color-table info-table\">";
        $at_location_con .= "<thead>";
        $at_location_con .= "<tr>";
        $at_location_con .= "<th>#</th>";
        $at_location_con .= "<th>" . __('dashboard.name') . "</th>";
        $at_location_con .= "<th>" . __('dashboard.check_in') . "</th>";
        $at_location_con .= "</tr>";
        $at_location_con .= "</thead>";
        $at_location_con .= "<tbody>";
        foreach ($at_location_person as $key => $bookings) {
            if ($key > 4) {
                break;
            }
            $count = $key + 1;
            $at_location_con .= "<tr>";
            $at_location_con .= "<td>" . $count . "</td>";
            $at_location_con .= "<td>" . $bookings->name . "</td>";
            $at_location_con .= "<td>" . $bookings->checkin . "</td>";
            $at_location_con .= "</tr>";
        }
        $at_location_con .= "</tbody>";
        $at_location_con .= "</table>";
        $at_location_con .= "</div>";
        if (count($at_location_person) > 0) {
            $at_location_con .= "<div class = \"row\">";
            $at_location_con .= "<div class = \"col-sm-12 text-center\">";
            $at_location_con .= "<a href=\"" . url('/currently_on_location_persons') . "\" class=\"btn btn-primary\">" . __('dashboard.view_more') . "</a>";
            $at_location_con .= "</div>";
            $at_location_con .= "</div>";
        }
        return array(
            'at_location_con' => $at_location_con
        );
    }

    /**
     * ajax based changes of dashboard transactions
     * @return type
     */
    public function get_at_location_transaction_details($das_p = FALSE)
    {
        $last_5_transactions = $this->dashboard_controller->get_last_5_transactions(5, $das_p);
        $at_location_con = "<div class = \"row\">";
        $at_location_con .= "<div class = \"col-sm-12\">";
        $at_location_con .= "<h4 class = \"box-title\">" . __('dashboard.last5_transaction') . "</h4>";
        $at_location_con .= "</div>";
        $at_location_con .= "</div>";
        $at_location_con .= "<div class = \"table-responsive row\">";
        $at_location_con .= "<table class = \"table color-table info-table\">";
        $at_location_con .= "<thead>";
        $at_location_con .= "<tr>";
        $at_location_con .= "<th>#</th>";
        $at_location_con .= "<th>" . __('dashboard.transaction') . "</th>";
        if (!$das_p) {
            $at_location_con .= "<th>" . __('dashboard.vehicle') . "</th>";
        }
        $at_location_con .= "<th>" . __('dashboard.check_in') . "</th>";
        $at_location_con .= "<th>" . __('dashboard.check_out') . "</th>";
        $at_location_con .= "</tr>";
        $at_location_con .= "</thead>";
        $at_location_con .= "<tbody>";
        foreach ($last_5_transactions as $key => $transaction) {
            if ($key > 4) {
                break;
            }
            $count = $key + 1;
            $at_location_con .= "<tr>";
            $at_location_con .= "<td>" . $count . "</td>";
            $at_location_con .= "<td><img src = \"" . $transaction->image . "\" class = \"img img-responsive h-50 w-50\"></td>";
            if (!$das_p) {
                $at_location_con .= "<td><a href = \"" . url('/vehicle/' . $transaction->booking_id) . "\" class = \"text-link\">" . $transaction->vehicle . "</a></td>";
            }
            $at_location_con .= "<td>" . $transaction->check_in . "</td>";
            $at_location_con .= "<td>" . $transaction->check_out . "</td>";
            $at_location_con .= "</tr>";
        }
        $at_location_con .= "</tbody>";
        $at_location_con .= "</table>";
        $at_location_con .= "</div>";
        if (count($last_5_transactions) > 0) {
            $at_location_con .= "<div class = \"row\">";
            $at_location_con .= "<div class = \"col-sm-12 text-center\">";
            if (auth()->user()->hasRole(['admin', 'manager', 'service', 'operator'])) {
                $at_location_con .= "<a href=\"" . url('/transaction_details') . "\" class=\"btn btn-primary\">" . __('dashboard.view_more') . "</a>";
            } else {
                $at_location_con .= "<a href=\"" . url('/transactions') . "\" class=\"btn btn-primary\">" . __('dashboard.view_more') . "</a>";
            }
            $at_location_con .= "</div>";
            $at_location_con .= "</div>";
        }
        return array(
            'at_location_con' => $at_location_con
        );
    }

    public function check_latest_device_transactions()
    {

        $results = array();
        //$devices = \App\LocationDevices::where('is_synched', 1)->get();
        $devices = \App\LocationDevices::whereIn('available_device_id', [1, 3, 6])
            ->orderBy('vehicle_device_sorting', 'asc')
            ->get();
        if ($devices->count() > 0) {
            foreach ($devices as $device) {
                if ($device->available_device_id != 6) {
                    $latest_transactions_html = '';
                    $transactions_html = $this->get_default_no_transaction();
                    $transactions = $this->dashboard_controller->get_device_transactions($device, FALSE, 5);
                    if (count($transactions) > 0) {
                        $transactions_html = '';
                        $transactions_html .= '<div class="col-md-9 col-xs-9">';
                        foreach ($transactions as $value) {
                            $transactions_html .= ' <div class="col-md-12 col-xs-12">
                                <a style="color:white!important" target="_blank" href="/transaction/1/' . $value['id'] . '">' . $value['content'] . '</a>
                            </div>';
                        }
                        $transactions_html .= '</div>
                        <div class="col-md-3 col-xs-3 text-center pt-35">
                            <a target="_blank" href="/device_transaction/tr/' . $device->id . '"><button class="btn btn-primary-transactions">More</button></a>
                        </div>';
                    }
                    $latest_transaction = $this->dashboard_controller->get_device_transactions($device, FALSE, 1);

                    if (count($latest_transaction) > 0) {
                        foreach ($latest_transaction as $value) {
                            $footer_latest_transaction = '<a style="color:white!important" target="_blank" href="/transaction/1/' . $value['id'] . '">' . $value['content'] . '</a>';
                            $latest_transactions_html .= '<div class="col-md-6 col-xs-6 text-right pt-5 pr-0 pl-0">
                                <button class="btn col-md-12 col-xs-12 btn-primary p-3 btn-open-gate btn-device-' . $device->id . '">' . $value['vehicle'] . '</button>
                            </div>
                            <div class="col-md-6 col-xs-6 text-right pt-5 pr-0 pl-0 btn-device-link-' . $device->id . '">
                                <a target="_blank" href="/transaction/1/' . $value['id'] . '" class="redirect_lnk"><button  class="btn col-md-12 col-xs-12 btn-primary p-3 btn-close-gate">View</button></a>
                            </div>';
                        }
                    } else {
                        $footer_latest_transaction = __('dashboard.no_transactions');
                        if ($device->has_related_ticket_reader == 1 || $device->has_gate) {
                            $latest_transactions_html .= '<div class="col-md-12 col-xs-12 text-center pt-5 pr-0 pl-0">
                                <i class="fa fa-warning f-25"></i>
                            </div>';
                        } else {
                            $latest_transactions_html .= '<div class="col-md-12 col-xs-12 text-center pt-5 pr-0 pl-0">
                                <i class="fa fa-warning f-90"></i>
                            </div>';
                        }
                    }
                    if ($device->has_gate) {
                        if ($device->is_opened == 1) {
                            $barrier_status = '';
                            $button_status = 'btn-primary';
                            if ($device->barrier_status == 1) {
                                $button_status = 'btn-danger';
                                $barrier_status = __('dashboard.locked_open');
                            } elseif ($device->barrier_status == 2) {
                                $button_status = 'btn-danger';
                                $barrier_status = __('dashboard.locked_closed');
                            } elseif ($device->barrier_status == 3) {
                                $button_status = 'btn-danger';
                                $barrier_status = __('dashboard.always_access');
                            }
                            $latest_transactions_html .= '<div class="col-md-12 col-xs-12 open_con text-right pt-5 pr-0 pl-0">
                                    <div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-open-gate-vehicle-active-con hidden">
                                    <button type="button" data-device_id="' . $device->id . '" class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate" onclick="open_gate_emergency_active(' . $device->id . ')">' . __('dashboard.open') . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                            if ($device->has_always_access) {
                                if ($device->barrier_status == 3) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                }
                                $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                            }
                            if ($device->barrier_status == 1) {
                                $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                            } else {
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                            }
                            if ($device->barrier_status == 2) {
                                $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                            } else {
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                            }
                            $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            if ($barrier_status != "") {
                                //                                $latest_transactions_html .= '<button class="btn col-md-12 col-xs-12 p-3 btn-danger btn-open-gate btn-open-gate-vehicle-non-active">' . $barrier_status . '</button>';
                                $latest_transactions_html .= '<div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-danger btn-open-gate-vehicle-non-active">
                                    <button class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate">' . $barrier_status . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                                if ($device->has_always_access) {
                                    if ($device->barrier_status == 3) {
                                        $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    } else {
                                        $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    }
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                                }
                                if ($device->barrier_status == 1) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                }
                                if ($device->barrier_status == 2) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                }
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            } else {
                                //                                $latest_transactions_html .= '<button class="btn col-md-12 col-xs-12 p-3 btn-primary btn-open-gate btn-open-gate-vehicle-non-active">Opened</button>';
                                $latest_transactions_html .= '<div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-primary btn-open-gate-vehicle-non-active">
                                    <button type="button" data-device_id="' . $device->id . '" class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate" onclick="open_gate_emergency_active(' . $device->id . ')">' . __('dashboard.open') . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                                if ($device->has_always_access) {
                                    if ($device->barrier_status == 3) {
                                        $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    } else {
                                        $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    }
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                                }
                                if ($device->barrier_status == 1) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                }
                                if ($device->barrier_status == 2) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                }
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            }
                            $latest_transactions_html .= '</div>
                                    <div class="col-md-12 col-xs-12 close_con text-right pt-5 pr-0 pl-0">
                                    <div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-close-gate-vehicle-active-con">
                                    <button type="button" data-device_id="' . $device->id . '" class="col-md-10 col-xs-10 btn btn-close-gate-group p-3 btn-close-gate" onclick="close_gate_emergency_active(' . $device->id . ')">' . __('dashboard.close') . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-close-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                            if ($device->has_always_access) {
                                if ($device->barrier_status == 3) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                }
                                $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                            }
                            if ($device->barrier_status == 1) {
                                $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                            } else {
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                            }
                            if ($device->barrier_status == 2) {
                                $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                            } else {
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                            }
                            $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            if ($barrier_status != "") {
                                //                                $latest_transactions_html .= '<button class="btn col-md-12 col-xs-12 p-3 btn-danger btn-close-gate btn-close-gate-vehicle-non-active hidden">' . $barrier_status . '</button>';
                                $latest_transactions_html .= '<div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-danger btn-close-gate-vehicle-non-active hidden">
                                    <button class="col-md-10 col-xs-10 btn btn-close-gate-group p-3 btn-close-gate">' . $barrier_status . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-close-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                                if ($device->has_always_access) {
                                    if ($device->barrier_status == 3) {
                                        $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    } else {
                                        $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    }
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                                }
                                if ($device->barrier_status == 1) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                }
                                if ($device->barrier_status == 2) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                }
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            } else {
                                //                                $latest_transactions_html .= '<button type="button" data-device_id="' . $device->id . '" class="btn col-md-12 col-xs-12 p-3 btn-primary btn-close-gate hidden" onclick="close_gate_emergency_active(' . $device->id . ')">' . __('dashboard.close') . '</button>';
                                $latest_transactions_html .= '<div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-primary btn-close-gate-vehicle-non-active hidden">
                                    <button type="button" data-device_id="' . $device->id . '" class="col-md-10 col-xs-10 btn btn-close-gate-group p-3 btn-close-gate" onclick="close_gate_emergency_active(' . $device->id . ')">' . __('dashboard.close') . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-close-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                                if ($device->has_always_access) {
                                    if ($device->barrier_status == 3) {
                                        $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    } else {
                                        $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    }
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                                }
                                if ($device->barrier_status == 1) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                }
                                if ($device->barrier_status == 2) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                }
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            }
                            $latest_transactions_html .= '</div>';
                        } else {
                            $barrier_status = '';
                            $button_status = 'btn-primary';
                            if ($device->barrier_status == 1) {
                                $button_status = 'btn-danger';
                                $barrier_status = __('dashboard.locked_open');
                            } elseif ($device->barrier_status == 2) {
                                $button_status = 'btn-danger';
                                $barrier_status = __('dashboard.locked_closed');
                            } elseif ($device->barrier_status == 3) {
                                $button_status = 'btn-danger';
                                $barrier_status = __('dashboard.always_access');
                            }
                            $latest_transactions_html .= '<div class="col-md-12 col-xs-12 open_con text-right pt-5 pr-0 pl-0">
                                    <div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-open-gate-vehicle-active-con">
                                    <button type="button" data-device_id="' . $device->id . '" class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate" onclick="open_gate_emergency_active(' . $device->id . ')">' . __('dashboard.open') . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                            if ($device->has_always_access) {
                                if ($device->barrier_status == 3) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                }
                                $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                            }
                            if ($device->barrier_status == 1) {
                                $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                            } else {
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                            }
                            if ($device->barrier_status == 2) {
                                $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                            } else {
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                            }
                            $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            if ($barrier_status != "") {
                                //                                $latest_transactions_html .= '<button class="btn col-md-12 col-xs-12 p-3 btn-danger btn-open-gate btn-open-gate-vehicle-non-active hidden">' . $barrier_status . '</button>';
                                $latest_transactions_html .= '<div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-danger btn-open-gate-vehicle-non-active hidden">
                                    <button class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate">' . $barrier_status . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                                if ($device->has_always_access) {
                                    if ($device->barrier_status == 3) {
                                        $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    } else {
                                        $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    }
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                                }
                                if ($device->barrier_status == 1) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                }
                                if ($device->barrier_status == 2) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                }
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            } else {
                                //                                $latest_transactions_html .= '<button class="btn col-md-12 col-xs-12 p-3 btn-primary btn-open-gate btn-open-gate-vehicle-non-active hidden">Opened</button>';
                                $latest_transactions_html .= '<div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-primary btn-open-gate-vehicle-non-active hidden">
                                    <button type="button" data-device_id="' . $device->id . '" class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate" onclick="open_gate_emergency_active(' . $device->id . ')">' . __('dashboard.open') . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                                if ($device->has_always_access) {
                                    if ($device->barrier_status == 3) {
                                        $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    } else {
                                        $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    }
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                                }
                                if ($device->barrier_status == 1) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                }
                                if ($device->barrier_status == 2) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                }
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            }
                            $latest_transactions_html .= '</div>
                                    <div class="col-md-12 col-xs-12 close_con text-right pt-5 pr-0 pl-0">
                                    <div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-close-gate-vehicle-active-con hidden">
                                    <button type="button" data-device_id="' . $device->id . '" class="col-md-10 col-xs-10 btn btn-close-gate-group p-3 btn-close-gate" onclick="close_gate_emergency_active(' . $device->id . ')">' . __('dashboard.close') . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-close-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                            if ($device->has_always_access) {
                                if ($device->barrier_status == 3) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                }
                                $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                            }
                            if ($device->barrier_status == 1) {
                                $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                            } else {
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                            }
                            if ($device->barrier_status == 2) {
                                $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                            } else {
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                            }
                            $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            if ($barrier_status != "") {
                                //                                $latest_transactions_html .= '<button class="btn col-md-12 col-xs-12 p-3 btn-danger btn-open-gate btn-open-gate-vehicle-non-active">' . $barrier_status . '</button>';
                                $latest_transactions_html .= '<div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-danger btn-close-gate-vehicle-non-active">
                                    <button class="col-md-10 col-xs-10 btn btn-close-gate-group p-3 btn-close-gate">' . $barrier_status . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-close-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                                if ($device->has_always_access) {
                                    if ($device->barrier_status == 3) {
                                        $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    } else {
                                        $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    }
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                                }
                                if ($device->barrier_status == 1) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                }
                                if ($device->barrier_status == 2) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                }
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            } else {
                                //                                $latest_transactions_html .= '<button type="button" data-device_id="' . $device->id . '" class="btn col-md-12 col-xs-12 p-3 btn-primary btn-close-gate" onclick="close_gate_emergency_active(' . $device->id . ')">' . __('dashboard.close') . '</button>';
                                $latest_transactions_html .= '<div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-primary btn-close-gate-vehicle-non-active">
                                    <button type="button" data-device_id="' . $device->id . '" class="col-md-10 col-xs-10 btn btn-close-gate-group p-3 btn-close-gate" onclick="close_gate_emergency_active(' . $device->id . ')">' . __('dashboard.close') . '</button>
                                    <button type="button" class="col-md-2 col-xs-2 btn btn-close-gate-group p-3 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">';
                                if ($device->has_always_access) {
                                    if ($device->barrier_status == 3) {
                                        $latest_transactions_html .= '<li style="display:none;">
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    } else {
                                        $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 3)">' . __('dashboard.always_access') . '</a>
                                        </li>';
                                    }
                                    $latest_transactions_html .= '<li>
                                        <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.verify_access') . '</a>
                                        </li>
                                        <li>
                                        <hr style="margin:5px;" />
                                        </li>';
                                }
                                if ($device->barrier_status == 1) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 1)">' . __('dashboard.locked_open') . '</a>
                                    </li>';
                                }
                                if ($device->barrier_status == 2) {
                                    $latest_transactions_html .= '<li style="display:none;">
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                } else {
                                    $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 2)">' . __('dashboard.locked_closed') . '</a>
                                    </li>';
                                }
                                $latest_transactions_html .= '<li>
                                    <a href="javascript:void(0)" onclick="change_barrier_status(' . $device->id . ', 0)">' . __('dashboard.unlock') . '</a>
                                    </li>
                                    </ul>
                                    </div>';
                            }
                            $latest_transactions_html .= '</div>';
                        }
                    }
                    $results[$device->id] = array(
                        'id' => $device->id,
                        'type' => $device->available_device_id,
                        'transactions_html' => $transactions_html,
                        'footer_latest_transaction' => $footer_latest_transaction,
                        'latest_transaction' => $latest_transactions_html,
                    );
                } else {
                    $vehicle_transactions_html = $this->get_default_no_transaction();
                    $vehicle_transactions = $this->dashboard_controller->get_vehicle_payment_transactions($device, 5);
                    if (count($vehicle_transactions) > 0) {
                        $vehicle_transactions_html = '';
                        $vehicle_transactions_html .= '<div class="col-md-9 col-xs-9">';
                        foreach ($vehicle_transactions as $value) {
                            $vehicle_transactions_html .= ' <div class="col-md-12 col-xs-12 p-0">
                                <a style="color:white!important" target="_blank" href="/transaction/2/' . $value['id'] . '">' . $value['content'] . '</a>
                            </div>';
                        }
                        $vehicle_transactions_html .= '</div>
                        <div class="col-md-3 col-xs-3 text-center pt-35">
                            <a target="_blank" href="/device_transaction/ptv/' . $device->id . '"><button class="btn btn-primary-transactions">More</button></a>
                        </div>';
                    }
                    $latest_vehicle_transaction = $this->dashboard_controller->get_vehicle_payment_transactions($device, 1);
                    if (count($latest_vehicle_transaction) > 0) {
                        foreach ($latest_vehicle_transaction as $value) {
                            $footer_latest_transaction = '<a style="color:white!important" target="_blank" href="/transaction/2/' . $value['id'] . '">' . $value['content'] . '</a>';
                        }
                    } else {
                        $footer_latest_transaction = 'No Transaction';
                    }
                    $results[$device->id] = array(
                        'id' => $device->id,
                        'type' => $device->available_device_id,
                        'footer_latest_transaction' => $footer_latest_transaction,
                        'vehicle_transactions_html' => $vehicle_transactions_html
                    );
                }
            }
        }
        return array(
            'results' => $results
        );
    }

    public function check_latest_device_transactions_p()
    {

        $results = array();
        //$devices = \App\LocationDevices::where('is_synched', 1)->get();
        $devices = \App\LocationDevices::whereIn('available_device_id', [2, 6])
            ->orderBy('person_device_sorting', 'asc')
            ->get();
        if ($devices->count() > 0) {
            foreach ($devices as $device) {
                if ($device->available_device_id != 6) {
                    $transactions_html = $this->get_default_no_transaction();
                    $transactions = $this->dashboard_controller->get_device_transactions($device, TRUE, 5);
                    if (count($transactions) > 0) {
                        $transactions_html = '';
                        $transactions_html .= '<div class="col-md-9 col-xs-9">';
                        foreach ($transactions as $value) {
                            $transactions_html .= ' <div class="col-md-12 col-xs-12">
                                <a style="color:white!important" target="_blank" href="/transaction/1/' . $value['id'] . '">' . $value['content'] . '</a>
                            </div>';
                        }
                        $transactions_html .= '</div>
                        <div class="col-md-3 col-xs-3 text-center pt-35">
                            <a target="_blank" href="/device_transaction/ptr/' . $device->id . '"><button class="btn btn-primary-transactions">More</button></a>
                        </div>';
                    }
                    $results[$device->id] = array(
                        'id' => $device->id,
                        'type' => $device->available_device_id,
                        'transactions_html' => $transactions_html
                    );
                } else {
                    $person_transactions_html = $this->get_default_no_transaction();
                    $person_transactions = $this->dashboard_controller->get_person_payment_transactions($device, 5);
                    if (count($person_transactions) > 0) {
                        $person_transactions_html = '';
                        $person_transactions_html .= '<div class="col-md-9 col-xs-9">';
                        foreach ($person_transactions as $value) {
                            $person_transactions_html .= ' <div class="col-md-12 col-xs-12">
                                <a style="color:white!important" target="_blank" href="/transaction/3/' . $value['id'] . '">' . $value['content'] . '</a>
                            </div>';
                        }
                        $person_transactions_html .= '</div>
                        <div class="col-md-3 col-xs-3 text-center pt-35">
                            <a target="_blank" href="/device_transaction/ptp/' . $device->id . '"><button class="btn btn-primary-transactions">More</button></a>
                        </div>';
                    }
                    $latest_person_transaction = $this->dashboard_controller->get_person_payment_transactions($device, 1);
                    if (count($latest_person_transaction) > 0) {
                        foreach ($latest_person_transaction as $value) {
                            $footer_latest_transaction = '<a style="color:white!important" target="_blank" href="/transaction/3/' . $value['id'] . '">' . $value['content'] . '</a>';
                        }
                    } else {
                        $footer_latest_transaction = 'No Transaction';
                    }
                    $results[$device->id] = array(
                        'id' => $device->id,
                        'type' => $device->available_device_id,
                        'footer_latest_transaction' => $footer_latest_transaction,
                        'person_transactions_html' => $person_transactions_html,
                    );
                }
            }
        }
        return array(
            'results' => $results
        );
    }

    function get_default_no_transaction()
    {
        ob_start();
?>
        <div class="col-md-12 col-xs-12">
            <p>No Transactions</p>
        </div>
<?php
        return ob_get_clean();
    }
}
