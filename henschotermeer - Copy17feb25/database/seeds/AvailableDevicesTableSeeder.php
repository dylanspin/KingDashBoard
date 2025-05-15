<?php

use Illuminate\Database\Seeder;
use App\AvailableDevices;
use App\Role;
use App\Permission;
use App\User;
use Illuminate\Support\Facades\DB;

class AvailableDevicesTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        //
        $device = AvailableDevices::where('name', '=', 'Ticket Readers')->first();
        if ($device == null) {
            $devices = new AvailableDevices();
            $devices->name = 'Ticket Readers';
            $devices->save();
        }

        $device = AvailableDevices::where('name', '=', 'Person Ticket Readers')->first();
        if ($device == null) {
            $devices = new AvailableDevices();
            $devices->name = 'Person Ticket Readers';
            $devices->save();
        }

        $device = AvailableDevices::where('name', '=', 'Plate Readers')->first();
        if ($device == null) {
            $devices = new AvailableDevices();
            $devices->name = 'Plate Readers';
            $devices->save();
        }

        $device = AvailableDevices::where('name', '=', 'Outdoor Display')->first();
        if ($device == null) {
            $devices = new AvailableDevices();
            $devices->name = 'Outdoor Display';
            $devices->save();
        }

        $device = AvailableDevices::where('name', '=', 'Camera')->first();
        if ($device == null) {
            $devices = new AvailableDevices();
            $devices->name = 'Camera';
            $devices->save();
            $devices->delete();
        }
        $device = AvailableDevices::where('name', '=', 'Payment Terminal')->first();
        if ($device == null) {
            $devices = new AvailableDevices();
            $devices->name = 'Payment Terminal';
            $devices->save();
        }
       
    }

}
