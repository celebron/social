<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource\interfaces;

interface SocialUserInterface
{
    public function getSocialField (string $social): string;
    public static function fieldSearch (string $field, mixed $id): ?self;
}