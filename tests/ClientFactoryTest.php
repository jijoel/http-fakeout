<?php

use HttpFakeout\ClientFactory;


class ClientFactoryTest extends PHPUnit_Framework_TestCase
{

    public $clientParameters = [
        'base_uri' => 'https://api.dev/v3/',
        'headers' => [
            "Content-type" => "application/json",
            "x-api-user" => 'user',
            "x-api-key" => 'token',
        ]
    ];

    /** @test */
    public function it_should_make_a_guzzle_client_by_default()
    {
        putenv('HTTP_FAKEOUT=');

        $test = ClientFactory::make(
            'path/to/files', 
            $this->clientParameters
        );

        $this->assertTrue($test instanceof GuzzleHttp\ClientInterface);
        $this->assertEquals('GuzzleHttp\Client', get_class($test));
    }

    /** @test */
    public function it_should_make_a_recorder()
    {
        putenv('HTTP_FAKEOUT=record');

        $test = ClientFactory::make(
            'path/to/files', 
            $this->clientParameters
        );

        $this->assertTrue($test instanceof GuzzleHttp\ClientInterface);
        $this->assertEquals('HttpFakeout\RecorderClient', get_class($test));
    }

    /** @test */
    public function it_should_make_a_player()
    {
        putenv('HTTP_FAKEOUT=playback');

        $test = ClientFactory::make(
            'path/to/files', 
            $this->clientParameters
        );

        $this->assertTrue($test instanceof GuzzleHttp\ClientInterface);
        $this->assertEquals('HttpFakeout\PlaybackClient', get_class($test));
    }

}