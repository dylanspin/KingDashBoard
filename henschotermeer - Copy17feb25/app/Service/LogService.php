<?php

namespace App\Service;

use App\RegressionTestingLog;
use Illuminate\Support\Facades\Session;
class LogService
{
    public $testingSessionId = false;
    public $type = false;
    public $description = false;
    public $filePath = false;

    public function  __construct($testingSessionId, $description, $filePath = false)
    {
        $this->testingSessionId = $testingSessionId;
        $this->description = $description;
        $this->filePath = $filePath;
        $this->info($this->testingSessionId, $this->description, $this->filePath);
    }
    public function info($testingSessionId, $description, $rule_id = false, $status = false, $file_path = false)
    {
		if(session()->exists("testing_session_id")){
			$infoLog = new RegressionTestingLog();
			$infoLog->testing_session_id = $testingSessionId;
			$infoLog->type = "info";
			$infoLog->rule_id = $rule_id;
			$infoLog->message = $description;
			if ($file_path) {
				$infoLog->file_path = $file_path;
			}
			if ($status) {
				//dd($testingSessionId, $description, $rule_id, $status, $file_path);
				$infoLog->status = $status;
			}
			$infoLog->save();
		}
    }
    public function debug($testingSessionId, $description, $rule_id = false, $status = false, $file_path = false)
    {
		if(session()->exists("testing_session_id")){
			$infoLog = new RegressionTestingLog();
			$infoLog->testing_session_id = $testingSessionId;
			$infoLog->type = "debug";
			$infoLog->message = $description;
			$infoLog->rule_id = $rule_id;
			if ($file_path) {
				$infoLog->file_path = $file_path;
			}
			if ($status) {
				$infoLog->status = $status;
			}
			$infoLog->save();
		}
    }
    public function error($testingSessionId, $description, $rule_id = false, $status = false, $file_path = false)
    {
		if(session()->exists("testing_session_id")){
			$infoLog = new RegressionTestingLog();
			$infoLog->type = "error";
			$infoLog->testing_session_id = $testingSessionId;
			$infoLog->message = $description;
			$infoLog->rule_id = $rule_id;
			if ($file_path) {
				$infoLog->file_path = $file_path;
			}
			if ($status) {
				$infoLog->status = $status;
			}
			$infoLog->save();
		}
    }
}
