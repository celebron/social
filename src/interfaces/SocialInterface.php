<?php

namespace Celebron\social\interfaces;

interface SocialInterface
{
    public function getSocialField(string $social):string;
}