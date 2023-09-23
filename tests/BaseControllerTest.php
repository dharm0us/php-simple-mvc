<?php
require_once 'test_setup.php';

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class BaseControllerTest extends PHPUnit\Framework\TestCase
{
    public function testDefaultAction()
    {
        $client = new Client(['base_uri' => 'http://127.0.0.1:8080']);
        $response = $client->get('/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Default Action.', $response->getBody());
    }

    public function testGetAction()
    {
        $client = new Client(['base_uri' => 'http://127.0.0.1:8080']);
        $response = $client->get('/?action=get_action&param1=p1&param2=p2');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('p1', $response->getBody());
    }

    public function testPostAction()
    {
        $client = new Client(['base_uri' => 'http://127.0.0.1:8080']);
        $postData = [
            'action' => 'post_action',
            'sampleParam' => 'testValue'
        ];

        $response = $client->post('/', [
            RequestOptions::FORM_PARAMS => $postData
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('testValue123', $response->getBody());
    }
}
