<?php

namespace Celebron\social\interfaces;

interface UrlsInterface
{
    public function getUriCode (): string;
    public function getUriToken (): string;
    public function getUriInfo (): string;
}