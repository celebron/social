<?php

namespace Celebron\socialSource\interfaces;

interface SocialUserInterface
{
    public function getSocialField (string $social): string;
}