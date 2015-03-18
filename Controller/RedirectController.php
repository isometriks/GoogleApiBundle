<?php

namespace Isometriks\Bundle\GoogleApiBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectController
{
    public function redirectAction($url)
    {
        return new RedirectResponse($url);
    }
}
