<?php
namespace HttpFakeout;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class PlaybackClient implements ClientInterface
{
    protected $file;
    protected $responses;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    // send a request object
    public function send(RequestInterface $request, array $options = [])
    {
        return $this->request(
            $request->getMethod(), 
            $request->getUri(),
            $options
        );
    }

    // create request object, and send it...
    public function request($method, $uri, array $options = [])
    {
        $this->init();

        $response = $this->find($method, $uri, $options);

        return $this->arrayToResponse($response);
    }

    protected function find($method, $uri, $options)
    {
        foreach($this->responses as $response) {
            if (strtolower($method) == strtolower($response['method'])
             && strtolower($uri) == strtolower($response['uri'])
             && json_encode($options) == $response['options']) {
                return $response['response'];
            }
        }

        throw new \Exception('The request was not found; run the recorder again?');
    }

    protected function init()
    {
        if ( is_null($this->responses)) {
            $contents = file_get_contents($this->file);
            $this->responses = json_decode($contents, true);
        }
    }


    /**
     * @param $item
     * @return Response[]
     */
    protected function arrayToResponse($item)
    {
        if (! $item['error']) {
            return new Response(
                $item['statusCode'], 
                json_decode($item['headers'], true),
                $item['body']
            );
        }

        $errorClass = $item['errorClass'];
        $request = new Request(
            $item['request']['method'],
            $item['request']['uri'],
            json_decode($item['request']['headers'], true),
            $item['request']['body']
        );
        throw new $errorClass(
            $item['errorMessage'], 
            $request
        );
    }


    // Throw errors for async methods

    public function getConfig($option = null)
    {
        throw new \Exception('This method has not been implemented');
    }

    public function sendAsync(RequestInterface $request, array $options = [])
    {
        throw new \Exception('This method has not been implemented');
    }

    public function requestAsync($method, $uri, array $options = [])
    {
        throw new \Exception('This method has not been implemented');
    }

    public function __call($method, $args)
    {
        if (count($args) < 1) {
            throw new \InvalidArgumentException('Magic request methods require a URI and optional options array');
        }

        $uri = $args[0];
        $opts = isset($args[1]) ? $args[1] : [];

        return substr($method, -5) === 'Async'
            ? $this->requestAsync(substr($method, 0, -5), $uri, $opts)
            : $this->request($method, $uri, $opts);
    }

}
