<?php

use HttpFakeout\PlaybackClient;


/** @group now */
class PlaybackClientTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_exception_for_invalid_magic_method()
    {
        $test = new PlaybackClient('data/data2.json');

        $test->get();
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function it_throws_exception_for_sendAsync()
    {
        $request = Mockery::mock('Psr\Http\Message\RequestInterface');
        $test = new PlaybackClient('data/data2.json');

        $test->sendAsync($request);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function it_throws_exception_for_requestAsync()
    {
        $test = new PlaybackClient('data/data2.json');

        $test->requestAsync('post', 'uri');
    }

    /**
     * @test
     * @dataProvider getMagicAsyncMethods
     * @expectedException \Exception
     */
    public function it_throws_exception_for_magic_async_methods($method)
    {
        $test = new PlaybackClient('data/data2.json');

        $test->$method('uri');
    }

    public function getMagicAsyncMethods()
    {
        return array(
            ['getAsync'],['deleteAsync'],['headAsync'],
            ['optionsAsync'],['patchAsync'],['postAsync'],
            ['putAsync'],
            ['getConfig']
        );
    }

}
