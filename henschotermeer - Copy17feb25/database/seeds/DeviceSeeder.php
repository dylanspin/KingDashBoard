<?php

use Illuminate\Database\Seeder;
use App\LocationDevices;
use App\LocationOptions;
class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $locationOption = LocationOptions::first();
        $locationId = $locationOption->live_id;
        $device = LocationDevices::where('available_device_id', 13)->first();
        if(!$device){
            $device = new LocationDevices();
        }
        $device->device_name = "Zebra Scanner";
        $device->available_device_id = 13;
        $device->device_direction = "bi-directional";
        $device->is_synched=0;
        $device->enable_log=0;
        $device->enable_idle_screen=0;
        $device->opacity_input=0;
        $device->camera_enabled=0;
          $device->has_gate=0;
        $device->has_barrier=0;
         $device->barrier_close_time= 0;
        $device->qr_code_type= 0;
        $device->confidence_level_lowest= 0;
        $device->character_match_limit=0;
        $device->is_opened = 0;
        $device->has_related_ticket_reader = 0;
        $device->has_sdl = 0;
        $device->gate_close_transaction_enabled = 0;
        $device->has_pdl = 0;
        $device->plate_correction_enabled = 0;
        $device->barrier_status = 0;
        $device->has_always_access = 0;
        $device->has_enable_person_ticket = 0;
        $device->has_enable_parking_ticket = 0;
        $device->popup_time = 0;
        $device->printer_name = 0;
        $device->is_imported = 0;
        $device->disable_night_mode = 0;
        $device->light_condition = 0;
        $device->emergency_entry_exit = 0;
        $device->save();
    }
}
