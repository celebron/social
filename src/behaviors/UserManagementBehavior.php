<?php

namespace Celebron\socialSource\behaviors;

use Celebron\socialSource\interfaces\SocialUserInterface;
use Celebron\socialSource\Response;
use Celebron\socialSource\ResponseSocial;
use Celebron\socialSource\Social;
use yii\db\ActiveQuery;
use yii\web\IdentityInterface;
use yii\web\UnauthorizedHttpException;

class UserManagementBehavior extends \yii\base\Behavior
{

    public ActiveQuery $query;
    public ?string $db = null;
    public int $rememberTime;

    /**
     * @throws UnauthorizedHttpException
     */
    public function socialLogin(ResponseSocial $response):bool
    {
        if(($this->owner instanceof SocialUserInterface) && ($this->owner instanceof IdentityInterface)) {
            $field = $this->owner->getSocialField($response->socialName);
            $this->query = $this->owner::find();
            $login = $this->query->one($this->db);
            if (is_null($login)) {
                throw new UnauthorizedHttpException('Not authorized');
            }
            return \Yii::$app->user->login($login, $this->rememberTime);
        }
    }
    /**
     * @throws \Exception
     */
    public function socialRegister (ResponseSocial $response): Response
    {
        return Response::saveModel($response, $this->owner);
    }

    /**
     * @throws \Exception
     */
    public function socialDelete (Social $social): Response
    {
        return Response::saveModel($social, $this->owner);
    }
}