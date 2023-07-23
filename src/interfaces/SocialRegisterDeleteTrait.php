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
        return Response::saveModel($response, $this, false);
    }

    /**
     * @throws \Exception
     */
    public function socialDelete(OAuth2 $social):Response
    {
//        $social->getSocialId()
//        return Response::saveModel($response, $this, true);
    }
}