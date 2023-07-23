<?php

namespace Celebron\social\interfaces;

use Celebron\social\Response;
use Celebron\social\SocialResponse;
trait SocialRegisterDeleteTrait
{
    /**
     * @throws \Exception
     */
    public function socialRegister(SocialResponse $response):Response
    {
        return Response::saveModel($response, $this, false);
    }

    /**
     * @throws \Exception
     */
    public function socialDelete(SocialResponse $response):Response
    {
        return Response::saveModel($response, $this, true);
    }
}