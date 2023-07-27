<?php

namespace Celebron\socialSource\interfaces;

interface ViewerInterface
{
    public const VIEW_LOGIN = 'login';
    public const VIEW_MANAGEMENT = 'management';

    public function getSupportManagement (): bool;

    public function getSupportLogin (): bool;
}