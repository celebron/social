<?php

namespace Celebron\socialSource\behaviors;

use Celebron\socialSource\interfaces\SocialUserInterface;
use Celebron\socialSource\Response;
use Celebron\socialSource\ResponseSocial;
use Celebron\socialSource\Social;
use yii\base\NotSupportedException;
use yii\base\UnknownClassException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\UnauthorizedHttpException;

class UserManagementBehavior extends \yii\base\Behavior
{

    public ActiveQuery $query;
    public ?ActiveRecord $record = null;
    public ?string $db = null;
    public int $rememberTime;

    public function init ()
    {
        $this->query =  $this->owner::find();
    }

    /**
     * @throws UnauthorizedHttpException
     */
    public function socialLogin(ResponseSocial $response):bool
    {
        if(($this->owner instanceof SocialUserInterface) && ($this->owner instanceof IdentityInterface)) {
            $field = $this->owner->getSocialField($response->socialName);
            if (empty($this->record)) {
                $this->record = $this->query
                    ->andWhere([$field => $response->getId()])
                    ->one($this->db);
            }

            if (empty($this->record)) {
                throw new UnauthorizedHttpException(\Yii::t('social','Not authorized'));
            }
            return \Yii::$app->user->login($this->record, $this->rememberTime);
        }
        throw new NotSupportedException("Class {$this->owner::class} not support. Need implementation " . SocialUserInterface::class);
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