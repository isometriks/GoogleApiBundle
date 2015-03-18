<?php

namespace Isometriks\Bundle\GoogleApiBundle\Controller;

use Isometriks\Bundle\GoogleApiBundle\Event\ClientEvent;
use Isometriks\Bundle\GoogleApiBundle\Event\Events;
use Isometriks\Bundle\GoogleApiBundle\Event\OAuthEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OAuthController extends Controller
{
    public function authenticateAction(Request $request)
    {
        $scopeManager = $this->get('isometriks_google_api.scope_manager');
        $scopes = $scopeManager->prepareScope($request->get('scopes', array()));
        $client = $this->getClient();

        // Check if token is valid and has all required scopes
        if ($client->isAccessTokenExpired() || !$client->getAccessToken() || !$scopeManager->hasScopes($scopes)) {
            // Set Scopes
            $client->setScopes($scopes);

            // Set return URL
            if ($request->get('redirect_uri', false)) {
                $client->setState($request->get('redirect_uri'));
            }

            // Emit event before going to Auth URL
            $event = new ClientEvent($client);
            $this->get('event_dispatcher')->dispatch(Events::OAUTH_PRE_AUTH, $event);

            if (($response = $event->getResponse()) !== null) {
                return $response;
            }

            return $this->redirect($client->createAuthUrl());
        }

        // Already authenticated, so just redirect
        if ($request->get('redirect_uri', false)) {
            return $this->redirect($request->get('redirect_uri'));
        }

        return new Response('No redirect_uri, but authenticated.');
    }

    public function redirectAction(Request $request)
    {
        if (!$request->query->has('code')) {
            throw $this->createNotFoundException('No Code');
        }

        $client = $this->getClient();
        $client->authenticate($request->query->get('code'));

        $event = new OAuthEvent($client, $client->getAccessToken());
        $this->get('event_dispatcher')->dispatch(Events::OAUTH_TOKEN_RECEIVE, $event);

        return $this->redirect($this->generateUrl('isometriks_google_authenticate', array(
            'redirect_uri' => $request->get('state', null),
        )));
    }

    /**
     * @return \Google_Client Google Client
     */
    protected function getClient()
    {
        return $this->get('isometriks_google_api.client');
    }

    protected function getSession()
    {
        return $this->get('session');
    }
}
