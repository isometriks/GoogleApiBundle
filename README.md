Google API
==========

Installation
------------

``` bash
$ php composer.phar require isometriks/google-api-bundle dev-master
```

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
 * @GoogleScope({"gmail.readyonly"})
 */
class MyController extends Controller
{
    /**
     * @GoogleScope({"analytics.readonly", "calendar.readonly"})
     */
    public function indexAction()
    {
        // Will only execute if we have all 3 permissions
    }
}
```

Without Annotations
-------------------

```php
<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MyController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $scopeManager = $this->get('isometriks_google_api.scope_manager');

        if (!$scopeManager->hasScope('calendar.readonly')) {
            return new RedirectResponse($scopeManager->obtainScopeUrl(
                array('calendar.readonly'),
                $this->generateUrl('homepage')
            ));
        }

        // obtainScope(array $scopes, $returnUrl)
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

You can extend the abstract service we provide: `isometriks_google_api.storage.user_storage`
As noted in the above class you will need to either add setter injection or add an argument
to the constructor so you can make sure that the user is persisted in your ORM / other implementation.