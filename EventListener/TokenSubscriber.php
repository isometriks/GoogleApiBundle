<?php

namespace Isometriks\Bundle\GoogleApiBundle\EventListener;

use Isometriks\Bundle\GoogleApiBundle\Event\ClientEvent;
use Isometriks\Bundle\GoogleApiBundle\Event\Events;
use Isometriks\Bundle\GoogleApiBundle\Event\OAuthEvent;
use Isometriks\Bundle\GoogleApiBundle\Storage\StorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TokenSubscriber implements EventSubscriberInterface
{
    protected $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function onClientCreate(ClientEvent $event)
    {
        $client = $event->getClient();

        // Set token if exists
        if ($this->storage->hasToken()) {
            $client->setAccessToken($this->storage->getToken());
        }

        // Refresh the token if possible
        if ($client->getAccessToken() && $client->isAccessTokenExpired() && $client->getRefreshToken()) {

            // Refresk the token
            $client->refreshToken($client->getRefreshToken());

            // Store the new token
            $this->storage->setToken($client->getAccessToken());
        }
    }

    public function onReceiveToken(OAuthEvent $event)
    {
        $this->storage->setToken($event->getToken());
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::OAUTH_CLIENT_CREATE => 'onClientCreate',
            Events::OAUTH_TOKEN_RECEIVE => 'onReceiveToken',
        );
    }
}
