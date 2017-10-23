<?php

namespace Newsletter\Utils\Api;

use GuzzleHttp\Client;
use Nette\SmartObject;

class ApiHelper{
    use SmartObject;

    private $config;

    private $http;

    public function __construct(array $config){
        $this->config = $config;
        $this->http = new Client(isset($config['http']) && is_array($config['http']) ? $config['http'] : []);
    }
}
