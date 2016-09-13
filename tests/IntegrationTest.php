<?php

use GuzzleHttp\Psr7\Request;
use HttpFakeout\ClientFactory;


class IntegrationTest extends PHPUnit_Framework_TestCase
{
    public $file;

    public function setUp()
    {
        $this->file = __DIR__.'/../data/data.json';
    }

    /** @test */
    public function it_can_get_data_from_uri()
    {
        putenv('HTTP_FAKEOUT=record');
        $test = ClientFactory::make($this->file);

        $this->assertTrue($test instanceof GuzzleHttp\ClientInterface);
        $this->assertEquals('HttpFakeout\RecorderClient', get_class($test));

        $test->clear();
        $request = new Request('get', 'http://localhost:3000/todos/1');
        $response = $test->send($request);

        $data = json_decode((string)$response->getBody());
        $this->assertEquals(1, $data->id);

        $saved = file_get_contents($this->file);
        $this->assertContains('\"id\": 1', $saved);
    }

    /** @test */
    public function it_can_get_data_via_request()
    {
        putenv('HTTP_FAKEOUT=record');
        $test = ClientFactory::make($this->file);

        $response = $test->request('get', 'http://localhost:3000/todos/2');

        $data = json_decode((string)$response->getBody());
        $this->assertEquals(2, $data->id);

        $saved = file_get_contents($this->file);
        $this->assertContains('\"id\": 2', $saved);
    }

    /** @test */
    public function it_can_get_data_via_magic_method()
    {
        putenv('HTTP_FAKEOUT=record');

        $test = ClientFactory::make($this->file, [
            'base_uri' => 'http://localhost:3000/'
        ]);

        $response = $test->get('todos/3');

        $data = json_decode((string)$response->getBody());
        $this->assertEquals(3, $data->id);

        $saved = file_get_contents($this->file);
        $this->assertContains('\"id\": 3', $saved);
    }

    /** 
     * @test 
     * @expectedException GuzzleHttp\Exception\ClientException
     */
    public function it_can_get_an_error_from_uri()
    {
        putenv('HTTP_FAKEOUT=record');

        $test = ClientFactory::make($this->file);

        $this->assertTrue($test instanceof GuzzleHttp\ClientInterface);
        $this->assertEquals('HttpFakeout\RecorderClient', get_class($test));

        $request = new Request('get', 'http://localhost:3000/fizzbuzz/1');
        $response = $test->send($request);
    }

    /** @test */
    public function it_has_recorded_the_error()
    {
        $saved = file_get_contents($this->file);
        $this->assertContains('response":{"error":true', $saved);
        $this->assertContains('GET http:\/\/localhost:3000\/fizzbuzz\/1` resulted in a `404 Not Found` response', $saved);
    }

    /** 
     * @test
     * @group playback 
     */
    public function it_can_load_data_from_file()
    {
        putenv('HTTP_FAKEOUT=playback');

        $test = ClientFactory::make($this->file);

        $this->assertTrue($test instanceof GuzzleHttp\ClientInterface);
        $this->assertEquals('HttpFakeout\PlaybackClient', get_class($test));

        $request = new Request('get', 'http://localhost:3000/todos/2');
        $response = $test->send($request);

        $data = json_decode((string)$response->getBody());
        $this->assertEquals(2, $data->id);
    }

    /** 
     * @test
     * @group playback 
     */
    public function it_can_load_data_with_partial_url()
    {
        putenv('HTTP_FAKEOUT=playback');

        $test = ClientFactory::make($this->file);

        $request = new Request('get', 'http://localhost:3000/todos/3');
        $response = $test->send($request);

        $data = json_decode((string)$response->getBody());
        $this->assertEquals(3, $data->id);
    }

    /** 
     * @test
     * @group playback 
     */
    public function it_can_load_data_via_a_request()
    {
        putenv('HTTP_FAKEOUT=playback');

        $test = ClientFactory::make($this->file);

        $response = $test->request('get', 'http://localhost:3000/todos/2');

        $data = json_decode((string)$response->getBody());
        $this->assertEquals(2, $data->id);
    }

    /** 
     * @test
     * @group playback 
     */
    public function it_can_load_data_via_magic_method()
    {
        putenv('HTTP_FAKEOUT=playback');

        $test = ClientFactory::make($this->file);

        $response = $test->get('http://localhost:3000/todos/3');

        $data = json_decode((string)$response->getBody());
        $this->assertEquals(3, $data->id);
    }

    /** 
     * @test
     * @group playback 
     * @expectedException GuzzleHttp\Exception\ClientException
     */
    public function it_can_load_error_data()
    {
        putenv('HTTP_FAKEOUT=playback');

        $test = ClientFactory::make($this->file);

        $request = new Request('get', 'http://localhost:3000/fizzbuzz/1');
        $response = $test->send($request);
    }

    /** 
     * @test
     * @group playback 
     * @expectedException Exception
     */
    public function it_can_throw_an_exception_if_data_not_found()
    {
        putenv('HTTP_FAKEOUT=playback');

        $test = ClientFactory::make($this->file);

        $request = new Request('get', 'http://localhost:3000/missing_data');
        $response = $test->send($request);
    }

}
