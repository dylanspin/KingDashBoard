<?php

namespace App\Http\Controllers\Connection;

use App\Http\Controllers\Connection\Message;
use Exception;
use Auth;
use Config;

class Client {

    public $port;
    public $host;
    public $errnum = NULL;
    public $errstr = NULL;
    public $timeout = 5;

    public function __construct($host, $port) {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * 
     * @param type $command
     * @param type $data
     * @return string|int
     */
    public function send($command, $data) {
        try {
            $response = array();
            $message = new Message($command, $data);
            $serialized_message = $message->serialize();
            $socket = socket_create(AF_INET, SOCK_STREAM, 0);
            if ($socket === false) {
                $response['status'] = 1;
                $response['message'] = "Could not create socket";
                return $response;
            }
            if (socket_connect($socket, $this->host, $this->port) === false) {
                $response['status'] = 2;
                $response['message'] = "Could not connect to server";
                return $response;
            }
            if (socket_write($socket, $serialized_message, strlen($serialized_message)) === false) {
                $response['message'] = "Could not send data to server";
                $response['status'] = 3;
                return $response;
            }
//            socket_close($socket);
            $response['message'] = 'Success';
            $response['status'] = 4;
            return $response;
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'message' => $ex->getMessage(),
            );
        }
    }

}
