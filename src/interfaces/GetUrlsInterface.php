<?php

namespace Celebron\social\interfaces;


interface GetUrlsInterface
{
    public function getBaseUrl() : string;
    public function getUriCode(): string;
    public function getUriToken(): string;
}