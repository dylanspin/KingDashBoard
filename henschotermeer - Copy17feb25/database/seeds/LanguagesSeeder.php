<?php

use Illuminate\Database\Seeder;
use App\MessageType;

class LanguagesSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        //
        $type = MessageType::where('key', '=', 'system_error')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'System Error';
            $type->key = 'system_error';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'registration_number_error')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Registration Number Error';
            $type->key = 'registration_number_error';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'unauthorized')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Unauthorized';
            $type->key = 'unauthorized';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'unauthorized_whitelist')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Unauthorized Whitelist';
            $type->key = 'unauthorized_whitelist';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'too_early')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Too Early';
            $type->key = 'too_early';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'too_early_whitelist')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Too Early Whitelist';
            $type->key = 'too_early_whitelist';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'parking_close')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Parking Close';
            $type->key = 'parking_close';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'parking_close_whitelist')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Parking Close Whitelist';
            $type->key = 'parking_close_whitelist';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'user_blocked')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'User Blocked';
            $type->key = 'user_blocked';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'ticket_used')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Ticket Used';
            $type->key = 'ticket_used';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'in_progress')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'In Progress';
            $type->key = 'in_progress';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'idle_message')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Idle Message';
            $type->key = 'idle_message';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'settings_updated')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Settings Updated';
            $type->key = 'settings_updated';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'whitelist_updated')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Whitelist Updated';
            $type->key = 'whitelist_updated';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'userlist_updated')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Userlist Updated';
            $type->key = 'userlist_updated';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'blocked_user_updated')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Blocked User Updated';
            $type->key = 'blocked_user_updated';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'timings_updated')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Timings Updated';
            $type->key = 'timings_updated';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'whitelist_timings_updated')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Whitelist Timings Updated';
            $type->key = 'whitelist_timings_updated';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'enterance_text')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Enterance Text';
            $type->key = 'enterance_text';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'welcome_entrance')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Welcome Entrance';
            $type->key = 'welcome_entrance';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'db_connection_issue')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Db Connection Issue';
            $type->key = 'db_connection_issue';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'anti_passback_message')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Anti Passback Message';
            $type->key = 'anti_passback_message';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'ticket_read_issue')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Ticket Read Issue';
            $type->key = 'ticket_read_issue';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'unauthorized_reader_parking')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Unauthorized Reader Parking';
            $type->key = 'unauthorized_reader_parking';
            $type->save();
        }
        $type = MessageType::where('key', '=', 'unauthorized_reader_person')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Unauthorized Reader Person';
            $type->key = 'unauthorized_reader_person';
            $type->save();
        }
        $type = MessageType::where('key', 'anti_passback_message_barcode')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Anti Passback Message Barcode';
            $type->key = 'anti_passback_message_barcode';
            $type->save();
        }
        $type = MessageType::where('key', 'unknown')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Unknown';
            $type->key = 'unknown';
            $type->save();
        }
        $type = MessageType::where('key', 'welcome_entrance')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Welcome Entrance';
            $type->key = 'welcome_entrance';
            $type->save();
        }
        $type = MessageType::where('key', 'goodbye_exit')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Goodbye {0}';
            $type->key = 'goodbye_exit';
            $type->save();
        }
        $type = MessageType::where('key', 'already_at_location')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Already at Location';
            $type->key = 'already_at_location';
            $type->save();
        }
        $type = MessageType::where('key', 'group_device_access_denied')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Group Access Denied for Device';
            $type->key = 'group_device_access_denied';
            $type->save();
        }
        $type = MessageType::where('key', 'barcode_not_at_location')->first();
        if (!$type) {
            $type = new MessageType();
            $type->name = 'Barcode not at location';
            $type->key = 'barcode_not_at_location';
            $type->save();
        }
        $type = MessageType::where('key', 'successfull_vehicle_payment')->first();
        if (!$type) {
            $type = new MessageType();
        }
        $type->name = 'Successfull Vehicle Payment';
        $type->key = 'successfull_vehicle_payment';
        $type->save();

        $type = MessageType::where('key', 'successfull_person_payment')->first();
        if (!$type) {
            $type = new MessageType();
        }
        $type->name = 'Successfull person Payment';
        $type->key = 'successfull_person_payment';
        $type->save();

        $type = MessageType::where('key', 'goto_nearby_payment_terminal')->first();
        if (!$type) {
            $type = new MessageType();
        }
        $type->name = 'Goto Nearby Payment Terminal';
        $type->key = 'goto_nearby_payment_terminal';
        $type->save();

        $type = MessageType::where('key', 'goto_nearby_ticket_reader')->first();
        if (!$type) {
            $type = new MessageType();
        }
        $type->name = 'Goto Nearby Ticket Reader';
        $type->key = 'goto_nearby_ticket_reader';
        $type->save();
        $type = MessageType::where('key', 'ticket_reader_not_configured')->first();
        if (!$type) {
            $type = new MessageType();
        }
        $type->name = 'Related Ticket Reader Not Configured';
        $type->key = 'ticket_reader_not_configured';
        $type->save();
        
        
        $type = MessageType::where('key', 'payment_not_eligible')->first();
        if (!$type) {
            $type = new MessageType();
        }
        $type->name = 'Not Eligible For Payment';
        $type->key = 'payment_not_eligible';
        $type->save();
    }

}
