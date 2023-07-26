<?php

namespace Celebron\socialSource\interfaces;

interface UrlsInterface
{
    public function getBaseUrl() : string;
    public function getUriCode(): string;
    public function getUriToken(): string;
    public function getUriInfo(): string;

}