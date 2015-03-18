Google API
==========

Installation
------------

``` bash
$ php composer.phar require isometriks/google-api-bundle dev-master
```

Add routing:

```yaml
isometriks_google_api:
    resource: "@IsometriksGoogleApiBundle/Resources/config/routing.yml"
    prefix: /google
```

Note: Your `redirect_uri` below and in the developer console should match
that of the `isometriks_google_redirect` route. So if you do as above, then
redirect_uri below in dev environment should be:
http://example.com/app_dev.php/google/redirect and without app_dev.php for
production.

Configuration:
--------------

```yaml
isometriks_google_api:
    client:
        application_name:       ~
        client_id:              ~
        client_secret:          ~
        redirect_uri:           ~
        developer_key:          ~
        include_granted_scopes: false
        access_type:            online # or offline
    service:
        storage:                isometriks_google_api.storage.session
```

Controller Annotations
----------------------

```php
<?php

namespace AppBundle\Controller;

use Isometriks\Bundle\GoogleApiBundle\Annotation\GoogleScope;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @GoogleScope({"gmail.readonly"})
 */
class MyController extends Controller
{
    /**
     * @GoogleScope({"analytics.readonly", "calendar.readonly"})
     */
    public function indexAction()
    {
        // Will only execute if we have all 3 permissions

        // Use the services available in the Google PHP API
        $client = $this->get('isometriks_google_api.client');
        $analytics = new \Google_Service_Analytics($client);

        var_dump($analytics->management_accounts->listManagementAccounts()); // etc
    }
}
```

Without Annotations
-------------------

```php
<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MyController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $scopeManager = $this->get('isometriks_google_api.scope_manager');

        if (!$scopeManager->hasScope('calendar.readonly')) {
            return $this->redirect($scopeManager->obtainScopeUrl(
                array('calendar.readonly'),
                $this->generateUrl('homepage')
            ));
        }

        // obtainScopeUrl(array $scopes, $returnUrl)
    }
}
```

Token Storage
-------------

By default the system will use sessions to store the tokens. If you are going
to be using refresh tokens (`type = offline`) then this won't work well as
destroying the session will destroy your refresh token since you only get
one the very first time you authorize. In this case you can create your own
storage, or if you need to store tokens for users we provide an abstract class
to help with that.

Example of User storage:

```
<?php

namespace AppBundle\Storage;

use Isometriks\Bundle\GoogleApiBundle\Storage\UserStorage as BaseUserStorage;

class UserStorage extends BaseUserStorage
{
    public function getToken()
    {
        return $this->getUser()->getToken();
    }

    public function hasToken()
    {
        return $this->getUser() && $this->getUser()->getToken();
    }

    public function removeToken()
    {
        $this->getUser()->setToken(null);

        // Persist Doctrine / Propel etc..
    }

    public function setToken($token)
    {
        $this->getUser()->setToken($token);

        // Persist Doctrine / Propel etc..
    }
}

```

And remember to change the config:

```yaml
isometriks_google_api:
    service:
        storage: app_bundle.storage.user_storage
```

You can extend the abstract service we provide: `isometriks_google_api.storage.user_storage`
As noted in the above class you will need to either add setter injection or add an argument
to the constructor so you can make sure that the user is persisted in your ORM / other implementation.

Display a "Connect" screen
--------------------------

You can easily attach into the events to display a "connect" button view before
sending the user to Google for the token using the `Events::OAUTH_PRE_AUTH` event:

```
<?php

// ...

class PreAuthListener implements EventSubscriberInterface
{
    protected $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onPreAuth(ClientEvent $event)
    {
        // Or use your own template, ours is mostly just an example
        $content = $this->twig->render('IsometriksGoogleApiBundle:OAuth:connect.html.twig', array(
            'auth_url' => $event->getClient()->createAuthUrl(),
        ));

        $event->setResponse(new Response($content));
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::OAUTH_PRE_AUTH => 'onPreAuth',
        );
    }
}