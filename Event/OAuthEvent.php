<?php

namespace Isometriks\Bundle\GoogleApiBundle\Event;

class OAuthEvent extends ClientEvent
{
    protected $token;

    public function __construct(\Google_Client $client, $token)
    {
        parent::__construct($client);
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }
}
