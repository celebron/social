<?php

namespace Celebron\social\eventArgs;

class ErrorEventArgs extends SuccessEventArgs
{
    public bool $throw = true;
}