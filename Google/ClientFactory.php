<?php

namespace Isometriks\Bundle\GoogleApiBundle\Google;

use Isometriks\Bundle\GoogleApiBundle\Event\ClientEvent;
use Isometriks\Bundle\GoogleApiBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;

class ClientFactory
{
    protected $eventDispatcher;
    protected $router;
    protected $clientClass;
    protected $config;

    public function __construct(EventDispatcherInterface $eventDispatcher, RouterInterface $router, $clientClass, array $config)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
        $this->clientClass = $clientClass;
        $this->config = $config;
    }

    public function createClient()
    {
        $client = new $this->clientClass();
        $client->setApplicationName($this->config['application_name']);
        $client->setClientId($this->config['client_id']);
        $client->setClientSecret($this->config['client_secret']);
        $client->setRedirectUri($this->router->generate($this->config['redirect_route'], array(), true));
        $client->setDeveloperKey($this->config['developer_key']);
        $client->setIncludeGrantedScopes($this->config['include_granted_scopes']);
        $client->setAccessType($this->config['access_type']);

        $event = new ClientEvent($client);
        $this->eventDispatcher->dispatch(Events::OAUTH_CLIENT_CREATE, $event);

        return $client;
    }
}
