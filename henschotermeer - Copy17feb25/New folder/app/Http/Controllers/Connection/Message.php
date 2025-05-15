<?php

namespace App\Http\Controllers\Connection;

class Message {

    /**
     * The command used to trigger this message.
     *
     * @var string
     */
    public $command;

    /**
     * The inner data of the message.
     *
     * @var mixed
     */
    public $data;

    /**
     * Constructs a message.
     */
    public function __construct($command, $data) {
        $this->command = $command;
        $this->data = $data;
    }

    /**
     * Serializes the message.
     *
     * @var string
     */
    public function serialize() {
        return $this->data;
//        return json_encode([
//            'command' => $this->command,
//            'data' => $this->data
//        ]);
    }

}
