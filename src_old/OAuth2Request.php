<?php

namespace Celebron\src_old;

use Celebron\social\old\eventArgs\RequestArgs;
use Celebron\social\old\interfaces\RequestIdInterface;
use Celebron\social\old\interfaces\SetFullUrlInterface;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

#[\Attribute(\Attribute::TARGET_METHOD)]
class OAuth2Request
{
    /**
     * @throws InvalidConfigException
     * @throws Exception
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     */
    final public function request(OAuth2 $auth, RequestArgs $args): void
    {
        $session = \Yii::$app->session;
        if (!$session->isActive) {
            $session->open();
        }

        if ($args->code === null) {
            $request = new RequestCode($auth, $args->state);
            $auth->requestCode($request);
            $session['social_random'] = $request->state->random;
            $url = $auth->client->get($request->generateUri());
            if ($this instanceof SetFullUrlInterface) {
                $url->setFullUrl($this->setFullUrl($url));
            }
            //Перейти на соответвующую страницу
            \Yii::$app->response->redirect($url->getFullUrl(), checkAjax: false)->send();
            exit(0);
        }

        $equalRandom = $args->state->equalRandom($session['social_random']);
        \Yii::$app->session->remove('social_random');

        if ($equalRandom) {
            $request = new RequestToken($args->code, $auth);
            $auth->requestToken($request);
            if ($request->send) {
                $auth->token = $auth->sendToken($request);
            }
        } else {
            throw new BadRequestHttpException('Random not equal', code: 1);
        }

        if ($auth instanceof RequestIdInterface) {
            $requestId = new RequestId($auth);
            $requestId->uri = $auth->getUriInfo();
            $auth->id = $auth->requestId($requestId);

            \Yii::debug("User id: {$auth->id}", static::class);
        }

        if ($auth->id === null) {
            throw new NotFoundHttpException("User not found", code: 2);
        }
    }

}