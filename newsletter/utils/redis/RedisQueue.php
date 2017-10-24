<?php

namespace Newsletter\Utils\Redis;

use Predis\Client;

class RedisQueue{

    /** @var Client */
    private $client;

    private $queue;

    public function __construct(Client $client, $queue){
        $this->client = $client;
        $this->queue = $queue;
    }

    public function enqueue($data){
        $this->client->lpush($this->queue, json_encode($data));
        return $this;
    }

    public function dequeue(){
         return $this->decode($this->client->rpop($this->queue));
    }

    public function waitFor(){
        return $this->decode($this->client->brpop($this->queue, 0));
    }

    private function decode($result){
        list($res, $data) = $result;
        if($data){
            return json_decode($data, true);
        }
        return null;
    }
}
