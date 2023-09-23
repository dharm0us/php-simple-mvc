<?php
require_once 'test_setup.php';

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class BaseControllerTest extends PHPUnit\Framework\TestCase
{

    public static function setUpBeforeClass(): void
    {
        TestUtils::setUpTestDB();

        $player = new PlayerEntity();
        $player->setName('Ramesh');
        $player->save();

        $cat = new CategoryEntity();
        $cat->setCat('Boys');
        $cat->setSubCat('U12');
        $cat->save();
    }

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
        $response = $client->get('/?module=player&action=show&id=1');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Ramesh', $response->getBody());
    }

    public function testPostAction()
    {
        $client = new Client(['base_uri' => 'http://127.0.0.1:8080']);
        $postData = [
            'module' => 'category',
            'action' => 'show',
            'id' => '1'
        ];

        $response = $client->post('/', [
            RequestOptions::FORM_PARAMS => $postData
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Boys', $response->getBody());
    }
}
