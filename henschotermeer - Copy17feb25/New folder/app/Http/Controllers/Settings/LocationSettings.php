<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class LocationSettings extends Controller {

    public function __construct() {
        
    }

    public function generate_location_settings(Request $request, $key, $id = null) {
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
            $type = $valid_settings->device_direction;
            $location = array();
            $location_details = $this->get_location();
            if (!$location_details) {
                return array(
                    'status' => 0,
                    'message' => 'Invalid Access ',
                    'data' => FALSE,
                );
            }
            $device = array();
            $device['id'] = $valid_settings->id;
            $device['name'] = $valid_settings->device_name;
            $device['device_direction'] = $valid_settings->device_direction;
            $device_type = \App\AvailableDevices::find($valid_settings->available_device_id);
            if ($device_type) {
                $device['type'] = $device_type;
            }
            $location['device'] = $device;
            $location['id'] = $location_details->live_id;
            $location['title'] = $location_details->title;
            $location['address'] = $location_details->address;
            $location['avaialable_spots'] = $location_details->avaialable_spots;
            $location['total_spots'] = $location_details->total_spots;
            $city_country = $location_details->city_country;
            $city_country_array = explode('_', $city_country);
            $location['city'] = $city_country_array[0];
            $location['country'] = $city_country_array[1];
            $location['height_restriction_value'] = $location_details->height_restriction_value;
            $location['barcode_series'] = $location_details->barcode_series;
            $location['is_whitelist'] = $location_details->is_whitelist;
            $location_language_id = $location_details->language_id;
            $language_details = \App\Language::find($location_language_id);
            $location['lang'] = $language_details->code;
            $location['postal_code'] = str_replace(' ', '', $location_details->postal_code);
            $location['latitude'] = $location_details->latitude;
            $location['longitude'] = $location_details->longitude;

            return array(
                'status' => 1,
                'message' => 'Success',
                'data' => $location,
            );
        } catch (Exception $ex) {
            return array(
                'status' => 0,
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }

    function get_location() {
        $location_settings = \App\LocationOptions::first();
        if ($location_settings) {
            return $location_settings;
        }
        return FALSE;
    }

    function generate_location_timings(Request $request, $key, $id = null) {
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
            $location_timings_array = array();
            $parking_weekdays_timing = \App\LocationTimings::where('is_whitelist', 0)->get();
            if ($parking_weekdays_timing->count() > 0) {
                foreach ($parking_weekdays_timing as $value) {
                    if ($value->week_day_num == 0) {
                        $location_timings_array[] = array(
                            'day' => 'Sunday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                        continue;
                    } elseif ($value->week_day_num == 1) {
                        $location_timings_array[] = array(
                            'day' => 'Monday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    } elseif ($value->week_day_num == 2) {
                        $location_timings_array[] = array(
                            'day' => 'Tuesday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    } elseif ($value->week_day_num == 3) {
                        $location_timings_array[] = array(
                            'day' => 'Wednesday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    } elseif ($value->week_day_num == 4) {
                        $location_timings_array[] = array(
                            'day' => 'Thursday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    } elseif ($value->week_day_num == 5) {
                        $location_timings_array[] = array(
                            'day' => 'Friday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    } elseif ($value->week_day_num == 6) {
                        $location_timings_array[] = array(
                            'day' => 'Saturday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    }
                }
            }
            return array(
                'status' => 1,
                'message' => 'Success',
                'data' => $location_timings_array,
            );
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }

    function generate_location_whitelist_timings(Request $request, $key, $id = null) {
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
            $location_timings_array = array();
            $parking_weekdays_timing = \App\LocationTimings::where('is_whitelist', 1)->get();
            if ($parking_weekdays_timing->count() > 0) {
                foreach ($parking_weekdays_timing as $value) {
                    if ($value->week_day_num == 0) {
                        $location_timings_array[] = array(
                            'day' => 'Sunday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                        continue;
                    } elseif ($value->week_day_num == 1) {
                        $location_timings_array[] = array(
                            'day' => 'Monday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    } elseif ($value->week_day_num == 2) {
                        $location_timings_array[] = array(
                            'day' => 'Tuesday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    } elseif ($value->week_day_num == 3) {
                        $location_timings_array[] = array(
                            'day' => 'Wednesday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    } elseif ($value->week_day_num == 4) {
                        $location_timings_array[] = array(
                            'day' => 'Thursday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    } elseif ($value->week_day_num == 5) {
                        $location_timings_array[] = array(
                            'day' => 'Friday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    } elseif ($value->week_day_num == 6) {
                        $location_timings_array[] = array(
                            'day' => 'Saturday',
                            'open' => date('H:i', strtotime($value->opening_time)),
                            'close' => date('H:i', strtotime($value->closing_time))
                        );
                    }
                }
            }
            return array(
                'status' => 1,
                'message' => 'Success',
                'data' => $location_timings_array,
            );
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }

    public function location_settings_status(Request $request, $key, $id = null, $status) {
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
                $device_settings->location_settings = 1;
                $device_settings->location_settings_details = NULL;
            } else {
                $device_settings->location_settings = 0;
                $device_settings->location_settings_details = $error_message;
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

    public function location_timings_status(Request $request, $key, $id = null, $status) {
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
                $device_settings->location_timings_settings = 1;
                $device_settings->location_timings_settings_details = NULL;
            } else {
                $device_settings->location_timings_settings = 0;
                $device_settings->location_timings_settings_details = $error_message;
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

    public function location_whitelist_timings_status(Request $request, $key, $id = null, $status) {
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
                $device_settings->location_whitelist_timings_settings = 1;
                $device_settings->location_whitelist_timings_settings_details = NULL;
            } else {
                $device_settings->location_whitelist_timings_settings = 0;
                $device_settings->location_whitelist_timings_settings_details = $error_message;
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

}
