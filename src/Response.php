<?php

namespace Celebron\socialSource;

class Response
{
    public mixed $response; //Передача в success или failed
    public function __construct (
        public bool $success
    )
    {
    }
}