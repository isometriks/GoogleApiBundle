<?php

namespace Isometriks\Bundle\GoogleApiBundle\Google;

use Symfony\Component\Routing\RouterInterface;

class ScopeManager
{
    protected $client;
    protected $router;
    protected $tokenInfo;

    public function __construct(\Google_Client $client, RouterInterface $router)
    {
        $this->client = $client;
        $this->router = $router;
    }

    public function getTokenInfo()
    {
        if ($this->tokenInfo === null) {
            $oauth2 = new \Google_Service_Oauth2($this->client);
            $json = json_decode($this->client->getAccessToken(), true);

            try {
                $this->tokenInfo = $oauth2->tokeninfo(array(
                    'access_token' => $json['access_token'],
                ));
            } catch (\Exception $e) {
                $this->tokenInfo = array(
                    'scope' => '',
                );
            }
        }

        return $this->tokenInfo;
    }

    public function getScopes()
    {
        $tokenInfo = $this->getTokenInfo();

        if (empty($tokenInfo['scope'])) {
            return array();
        }

        return explode(' ', $tokenInfo['scope']);
    }

    public function hasScopes(array $scopes)
    {
        foreach ($scopes as $scope) {
            if (!$this->hasScope($scope)) {
                return false;
            }
        }

        return true;
    }

    public function hasScope($scope)
    {
        return in_array($this->prepareScope($scope), $this->getScopes());
    }

    public function prepareScope($scope)
    {
        if (is_array($scope)) {
            return array_map(array($this, 'prepareScope'), $scope);
        }

        if (0 !== strpos($scope, 'http')) {
            return 'https://www.googleapis.com/auth/'.$scope;
        }

        return $scope;
    }

    public function obtainScopeUrl(array $scopes, $redirectUri)
    {
        return $this->router->generate('isometriks_google_authenticate', array(
            'scopes' => $scopes,
            'redirect_uri' => $redirectUri,
        ));
    }
}
