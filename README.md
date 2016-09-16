HttpFakeout
===========

This is a self-initializing fake for php clients running Guzzle. It is primarily useful for php wrappers around API calls. It will record responses from the actual API, and play them back during testing, so you don't have to hit the actual endpoints while testing your application.

Please see http://martinfowler.com/bliki/SelfInitializingFake.html for more information about self-initizalizing fakes.


Installation
-------------
Install the package using composer. Since this project is not yet on packagist, you will need to include the repository in your config.json file, as shown below:

    "require": {
        "jijoel/http-fakeout": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "https://github.com/jijoel/http-fakeout"
        }
    ],

Once this is done `composer install` or `composer update` will load the package.


Usage
--------
HttpFakeout uses the HTTP_FAKEOUT environment variable to decide whether to record responses from the API or whether to play back those responses. If you want to record responses, set HTTP_FAKEOUT to "record". If you want to play them back, set it to "playback". Any other value (or no value at all) will ignore HttpFakeout, and hit the live endpoint directly.

The easiest way to do this, in practice, is to set up two configuration files for phpunit. In `phpunit.xml`, include this:

    <php>
        <env name="HTTP_FAKEOUT" value="playback"/>
    </php>

Copy phpunit.xml to another file, phpunit-record.xml. That file should contain this:

    <php>
        <env name="HTTP_FAKEOUT" value="record"/>
    </php>

Instead of creating a GuzzleHttp\Client object directly, use the HttpFakeout\ClientFactory object to create a client object for you. 

    ClientFactory::make('path/to/data', [client options]);

The ClientFactory will return one of the following objects

    env setting     Object returned
    record          HttpFakeout\RecorderClient
    playback        HttpFakeout\PlaybackClient
    n/a             GuzzleHttp\Client


Known Limitations
==================
HttpFakeout currently can not deal with asynchronous methods, or asynchronous method calls. It will throw an exception if they are encountered.


Development
============
In order to do integration testing for HttpFakeout, we need to reach a live api. Fortunately, we can install and use a fake api locally. [JSON Server](https://github.com/typicode/json-server) will give us a "full fake REST API with zero coding in less than 30 seconds." To install it, and the data that we use during the integration testing, run the following commands from the project root:

    sudo npm install -g json-server
    wget http://jsonplaceholder.typicode.com/db -O json-server/db.json
    json-server json-server/db.json

This is also saved as `bin/install-json-server`. Data will be available at http://localhost:3000/

