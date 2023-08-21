<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\interfaces;

interface SocialUserInterface
{
    public function getSocialField (string $social): string;
    public static function fieldSearch (string $field, mixed $id): ?self;
}