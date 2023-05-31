<?php

namespace Celebron\social\old\interfaces;

trait GetUrlsTrait
{

    public function getBaseUrl() : string
    {
        return $this->clientUrl;
    }
    public function getUriCode(): string
    {
        return $this->uriCode;
    }
    public function getUriToken(): string
    {
        return $this->uriToken;
    }
}