<?php

namespace Isometriks\Bundle\GoogleApiBundle\Storage;

interface StorageInterface
{
    public function hasToken();
    public function setToken($token);
    public function getToken();
    public function removeToken();
}