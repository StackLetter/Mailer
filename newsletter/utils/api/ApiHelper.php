<?php

namespace Newsletter\Utils\Api;

use GuzzleHttp\Client;
use Nette\SmartObject;

/**
 * @todo Add checks for non-existent config/definition keys
 */
class ApiHelper{
    use SmartObject;

    private $config;

    private $http;

    public function __construct(array $config){
        $this->config = $config;
        $this->http = new Client(isset($config['http']) && is_array($config['http']) ? $config['http'] : []);
    }

    public function getJson($uri){
        $response = $this->http->get($uri);
        if($response->getStatusCode() == 200){
            return json_decode($response->getBody(), true);
        }
        return false;
    }

    public function getNewsletterStructure($user_id, $frequency){
        return $this->getJson(sprintf($this->config['structure_endpoint'], $user_id, $frequency));
    }

    public function getSectionContent(array $definition, $user_id, $frequency, $contentIds){
        $duplicates = urlencode(json_encode(empty($contentIds) ? new \stdClass : $contentIds));
        $data = $this->getJson(sprintf($definition['content_endpoint'], $user_id, $frequency, $duplicates));
        return !$data ? [] : (is_array($data) ? array_slice($data, 0, $definition['limit']) : $data);
    }

    public function getUnsubscribeLink($user_id){
        $res = $this->getJson(sprintf($this->config['unsubscribe_endpoint'], $user_id));
        if($res['status'] == 'success'){
            return $res['link'];
        }
        return '#';
    }
}
