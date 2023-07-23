<?php

namespace Celebron\social\interfaces;

use Celebron\social\attrs\SocialRequest;
use Celebron\social\SocialResponse;
trait SocialRegisterDeleteTrait
{
    /**
     * @throws \Exception
     */
    public function socialRegister(SocialResponse $response):bool
    {
        return $response->saveModel($this, false);
    }

    /**
     * @throws \Exception
     */
    #[SocialRequest(false)]
    public function socialDelete(SocialResponse $response):bool
    {
        return $response->saveModel($this, true);
    }
}