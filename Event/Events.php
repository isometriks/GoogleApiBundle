<?php

namespace Isometriks\Bundle\GoogleApiBundle\Event;

final class Events
{
    const OAUTH_CLIENT_CREATE = 'isometriks_google_api.client.create';
    const OAUTH_PRE_AUTH = 'isometriks_google_api.pre_auth';
    const OAUTH_TOKEN_RECEIVE = 'isometriks_google_api.token.receive';
}
