<?php
namespace HttpFakeout;

use GuzzleHttp\Client;


class ClientFactory
{
    public static function make($destination = null, array $options = [])
    {
        $destination = $destination ?: __DIR__.'/../tests/data.json';
        
        $fakeout = getenv('HTTP_FAKEOUT');

        if ($fakeout === 'playback')
            return new PlaybackClient($destination);

        if ($fakeout === 'record')
            return new RecorderClient(
                $destination, 
                new Client($options)
            );

        return new Client($options);
    }

}