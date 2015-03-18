<?php

namespace Isometriks\Bundle\GoogleApiBundle\Storage;

use Symfony\Component\Security\Core\SecurityContextInterface;

abstract class UserStorage implements StorageInterface
{
    protected $context;

    public function __construct(SecurityContextInterface $context)
    {
        $this->context = $context;
    }

    protected function getUser()
    {
        return $this->context->getToken()->getUser() ?: false;
    }
}
