<?php
namespace HttpFakeout;

use Mockery;


class RecorderClientTest extends \PHPUnit_Framework_TestCase
{
    public $request;
    public $response;

    public function setUp()
    {
        $this->request = Mockery::mock('Psr\Http\Message\RequestInterface')
            ->shouldReceive('getMethod')->andReturn('get')
            ->shouldReceive('getUri')->andReturn('http://foo/bar')
            ->shouldReceive('getHeaders')->andReturn([])
            ->shouldReceive('getBody')->andReturn('')
            ->getMock();

        $this->response = Mockery::mock('Psr\Http\Message\ResponseInterface')
            ->shouldReceive('getStatusCode')->andReturn(200)
            ->shouldReceive('getRequest')->andReturn($this->request)
            ->shouldReceive('getHeaders')->andReturn([])
            ->shouldReceive('getBody')->andReturn('body')
            ->getMock();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /** @test */
    public function it_can_send_a_request()
    {
        $mock = Mockery::mock('GuzzleHttp\Client')
            ->shouldReceive('send')->once()->with($this->request, [])
            ->andReturn($this->response)
            ->getMock();

        $test = new RecorderClient('data/data2.json', $mock);
        $result = $test->send($this->request);

        $this->assertEquals($this->response, $result);
    }

    /** 
     * @test
     * @expectedException GuzzleHttp\Exception\ClientException
     */
    public function it_can_send_a_request_and_write_an_exception()
    {
        $response = Mockery::mock('GuzzleHttp\Exception\ClientException')
            ->shouldReceive('getRequest')->andReturn($this->request)
            ->getMock();

        $mock = Mockery::mock('GuzzleHttp\Client')
            ->shouldReceive('send')->once()->with($this->request, [])
            ->andThrow($response)
            ->getMock();

        $test = new RecorderClient('data/data2.json', $mock);
        $result = $test->send($this->request);
    }

    /** @test */
    public function it_can_make_a_request()
    {
        $mock = Mockery::mock('GuzzleHttp\Client')
            ->shouldReceive('request')->once()->with('get', 'http://foo/bar', [])
            ->andReturn($this->response)
            ->getMock();

        $test = new RecorderClient('data/data2.json', $mock);
        $result = $test->request('get','http://foo/bar');

        $this->assertEquals($this->response, $result);
    }

    /** 
     * @test
     * @expectedException GuzzleHttp\Exception\ClientException
     */
    public function it_can_make_a_request_and_write_an_exception()
    {
        $response = Mockery::mock('GuzzleHttp\Exception\ClientException')
            ->shouldReceive('getRequest')->andReturn($this->request)
            ->getMock();

        $mock = Mockery::mock('GuzzleHttp\Client')
            ->shouldReceive('request')->once()->with('get', 'http://foo/bar', [])
            ->andThrow($response)
            ->getMock();

        $test = new RecorderClient('data/data2.json', $mock);
        $result = $test->request('get','http://foo/bar');
    }

    /** @test */
    public function it_can_get_config()
    {
        $mock = Mockery::mock('GuzzleHttp\Client')
            ->shouldReceive('getConfig')->once()
            ->andReturn('foo')
            ->getMock();

        $test = new RecorderClient('data/data2.json', $mock);

        $result = $test->getConfig(null);

        $this->assertEquals('foo', $result);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_exception_for_invalid_magic_method()
    {
        $request = Mockery::mock('Psr\Http\Message\RequestInterface');
        $mock = Mockery::mock('GuzzleHttp\Client');
        $test = new RecorderClient('data/data2.json', $mock);

        $test->get();
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function it_throws_exception_for_sendAsync()
    {
        $request = Mockery::mock('Psr\Http\Message\RequestInterface');
        $mock = Mockery::mock('GuzzleHttp\Client');
        $test = new RecorderClient('data/data2.json', $mock);

        $test->sendAsync($request);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function it_throws_exception_for_requestAsync()
    {
        $mock = Mockery::mock('GuzzleHttp\Client');
        $test = new RecorderClient('data/data2.json', $mock);

        $test->requestAsync('post', 'uri');
    }

    /**
     * @test
     * @dataProvider getMagicAsyncMethods
     * @expectedException \Exception
     */
    public function it_throws_exception_for_magic_async_methods($method)
    {
        $mock = Mockery::mock('GuzzleHttp\Client');
        $test = new RecorderClient('data/data2.json', $mock);

        $test->$method('uri');
    }

    public function getMagicAsyncMethods()
    {
        return array(
            ['getAsync'],['deleteAsync'],['headAsync'],
            ['optionsAsync'],['patchAsync'],['postAsync'],
            ['putAsync'],
        );
    }

}

// function file_put_contents($file, $data, $options)
// {
//     // do not write... 
// }
