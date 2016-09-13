<?php
namespace HttpFakeout;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class RecorderClient implements ClientInterface
{
    protected $dest;
    protected $client;

    public function __construct(string $dest, ClientInterface $client)
    {
        $this->dest = $dest;
        $this->client = $client;
    }

    public function send(RequestInterface $request, array $options = [])
    {
        try {
            $response = $this->client->send($request, $options);
            $this->store(
                $request->getMethod(), 
                $this->buildUri($request->getUri()),
                $options,
                $response
            );
            return $response;
        } catch (\Exception $e) {
            $this->storeError(
                $request->getMethod(), 
                $request->getUri(),
                $options,
                $e
            );
            throw $e;
        }
    }

    public function request($method, $uri, array $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);
            $this->store($method, $uri, $options, $response);
            return $response;
        } catch (\Exception $e) {
            $this->storeError($method, $uri, $options, $e);
            throw $e;
        }
    }

    private function store($method, $uri, array $options, $response)
    {
        $this->writeData([
            'method' => (string)$method,
            'uri' => $this->buildUri($uri),
            'options' => json_encode($options),
            'response' => [
                'error' => false,
                'statusCode' => $response->getStatusCode(),
                'headers' => json_encode($response->getHeaders()),
                'body' => (string)$response->getBody()
            ]
        ]);
    }


    protected function storeError($method, $uri, $options, $exception)
    {
        $this->writeData([
            'method' => (string)$method,
            'uri' => $this->buildUri($uri),
            'options' => json_encode($options),
            'response' => [
                'error' => true,
                'errorClass' => get_class($exception),
                'errorMessage' => $exception->getMessage(),
                'request' => [
                    'method' => $exception->getRequest()->getMethod(),
                    'uri' => (string) $exception->getRequest()->getUri(),
                    'headers' => json_encode($exception->getRequest()->getHeaders()),
                    'body' => (string)$exception->getRequest()->getBody(),
                ]
            ]
        ]);
    }

    protected function writeData($data)
    {
        $file = $this->getDestination();

        $contents = file_get_contents($file);
        $trimmed = trim($contents, '[');
        $trimmed = trim($trimmed, ']');

        $separator = (strlen($trimmed)>0) ? ',' : '';

        file_put_contents(
            $file, 
            '[' . $trimmed . $separator . json_encode($data) . ']'
        );
    }

    public function clear()
    {
        $file = $this->getDestination();

        file_put_contents($file, null);
    }

    protected function getDestination()
    {
        $folder = pathinfo($this->dest)['dirname'];
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        return $this->dest;
    }

    protected function buildUri($uri)
    {
        $path = (string) $uri;

        if (strtolower(substr($path, 0, 4) == 'http'))
            return $path;

        $base = $this->client->getConfig('base_uri');

        return trim($base,'/') . '/' . $path;
    }

    public function getConfig($option = null)
    {
        return $this->client->getConfig($option);
    }

    // Throw errors for async methods

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

