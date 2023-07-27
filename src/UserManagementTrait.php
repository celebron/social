<?php

namespace Celebron\socialSource;

trait UserManagementTrait
{
    /**
     * @throws \Exception
     */
    public function socialRegister (ResponseSocial $response): Response
    {
        return Response::saveModel($response, $this);
    }

    /**
     * @throws \Exception
     */
    public function socialDelete (Request $social): Response
    {
        return Response::saveModel($social, $this);
    }

}