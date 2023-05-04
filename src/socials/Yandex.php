<?php /** @noinspection MissedFieldInspection */


namespace Celebron\social\socials;


use Celebron\social\interfaces\RequestIdInterface;
use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\GetUrlsTrait;
use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\interfaces\ToWidgetTrait;
use Celebron\social\Request;
use Celebron\social\RequestCode;
use Celebron\social\RequestId;
use Celebron\social\RequestToken;
use Celebron\social\Social;
use Celebron\social\SocialConfiguration;
use Celebron\social\WidgetSupport;
use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;


/**
 *
 * @property-read string $baseUrl
 */
#[WidgetSupport]
class Yandex extends Social implements GetUrlsInterface, ToWidgetInterface, RequestIdInterface
{
    use ToWidgetTrait, GetUrlsTrait;

    const METHOD_MAILTOKEN = 'mailToken';
    public string $clientUrl = "https://oauth.yandex.ru";
    public string $uriCode = 'authorize';
    public string $uriToken = 'token';
    public string $uriInfo = 'https://login.yandex.ru/info';


//    public string $icon = 'https://yastatic.net/s3/doc-binary/freeze/ru/id/228a1baa2a03e757cdee24712f4cc6b2e75636f2.svg';
//    public ?string $name;
//    public bool $visible = true;

    public ?string $fileName = null;

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function requestId (RequestId $request): mixed
    {
        $login = $request->getHeaderOauth(['format'=> 'json']);
        return $this->sendToField($login, 'id');
    }

    /**
     * @throws BadRequestHttpException
     */
    public function requestCode (RequestCode $request) : void
    {
        $get = Yii::$app->request->get();
        if (isset($get['error'])) {
            throw new BadRequestHttpException("[Yandex]Error: {$get['error']}. {$get['error_description']}");
        }
    }

    public function requestToken (RequestToken $request): void
    {
        $request->setAuthorizationBasic($this->clientId . ':' . $this->clientSecret);
    }

    public function getUriInfo (): string
    {
        return $this->uriInfo;
    }

    #[Request]
    public function mailToken():bool
    {
        if($this->token !== null && $this->fileName !== null) {
            return $this->token->create($this->fileName) !== false;
        }
        return false;
    }

    public static function urlMailToken(?string $state= null): string
    {
        return static::url(self::METHOD_MAILTOKEN, $state);
    }

}
