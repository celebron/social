<?php

namespace Celebron\social\interfaces;

trait GetUrlsTrait
{
    public string $clientUrl;
    public string $uriCode;
    public string $uriToken;

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