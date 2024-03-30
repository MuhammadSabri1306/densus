<?php
use GuzzleHttp\Client;

defined('BASEPATH') OR exit('No direct script access allowed');

class Rest_client
{
    private $baseUri;
    public $request = [];
    private $latestRequestData = [];

    public function __construct($config = [])
    {
        $this->baseUri = isset($config['baseUri']) ? $config['baseUri'] : null;
        $this->request['verify'] = false;
        $this->request['query'] = [];
        $this->request['headers'] = [ 'Accept' => 'application/json' ];
        $this->latestRequestData['method'] = null;
        $this->latestRequestData['url'] = null;
    }

    public function get_latest_request()
    {
        return $this->latestRequestData;
    }

    public function sendRequest(string $httpMethod, string $url, $associative = null)
    {
        $client = new Client();
        $this->latestRequestData['method'] = $httpMethod;
        $this->latestRequestData['url'] = $url;

        $response = $client->request($httpMethod, $url, $this->request);
        $body = $response->getBody();
        return json_decode($body, $associative);
    }
}