<?php

namespace Isometriks\Bundle\GoogleApiBundle\Storage;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionStorage implements StorageInterface
{
    const TOKEN_KEY = 'isometriks_google_api.token';

    protected $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function getToken()
    {
        return $this->session->get(self::TOKEN_KEY);
    }

    public function hasToken()
    {
        return $this->session->has(self::TOKEN_KEY);
    }

    public function removeToken()
    {
        $this->session->remove(self::TOKEN_KEY);
    }

    public function setToken($token)
    {
        $this->session->set(self::TOKEN_KEY, $token);
    }
}