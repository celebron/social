<?php

namespace Celebron\social\interfaces;

use Celebron\social\OAuth2;
use Celebron\social\Response;
use Celebron\social\SocialResponse;
trait SocialRegisterDeleteTrait
{
    /**
     * @throws \Exception
     */
    public function socialRegister(SocialResponse $response):Response
    {
        return Response::saveModel($response, $this);
    }

    /**
     * @throws \Exception
     */
    public function socialDelete(OAuth2 $social):Response
    {
        return Response::saveModel($social, $this);
    }
}