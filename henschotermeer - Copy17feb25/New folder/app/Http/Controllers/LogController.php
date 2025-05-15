<?php

namespace App\Http\Controllers;

class LogController extends Controller {

    public function __construct() {
        
    }

    public function log_create($type, $message, $stack = NULL) {
        $error_logs = new \App\ErrorLogs();
        $error_logs->type = $type;
        $error_logs->error_message = substr($message, 0, 500);
        $error_logs->error_stack = $stack;
        $error_logs->save();
    }

}
