<?php

namespace Isometriks\Bundle\GoogleApiBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class ClientEvent extends Event
{
    protected $client;
    protected $response;

    public function __construct(\Google_Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return \Google_Client $client
     */
    public function getClient()
    {
        return $this->client;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response $response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
