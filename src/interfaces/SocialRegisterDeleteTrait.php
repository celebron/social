<?php

namespace Celebron\social\interfaces;

use Celebron\social\attrs\Request;
use Celebron\social\Response;
trait SocialRegisterDeleteTrait
{
    /**
     * @throws \Exception
     */
    public function socialRegister(Response $response):bool
    {
        return $response->saveModel($this, false);
    }

    /**
     * @throws \Exception
     */
    #[Request(false)]
    public function socialDelete(Response $response):bool
    {
        return $response->saveModel($this, true);
    }
}