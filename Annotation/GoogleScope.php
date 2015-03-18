<?php

namespace Isometriks\Bundle\GoogleApiBundle\Annotation;

/**
 * @Annotation
 */
class GoogleScope
{
    public function __construct($options)
    {
        $this->scopes = (array)$options['value'];
    }

    public function getScopes()
    {
        return $this->scopes;
    }
}