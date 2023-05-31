<?php

namespace Celebron\social\old\interfaces;


interface GetUrlsInterface
{
    public function getBaseUrl() : string;
    public function getUriCode(): string;
    public function getUriToken(): string;
    public function getUriInfo(): string;
}