<?php /** @noinspection MissedFieldInspection */


namespace Celebron\social\old\socials;


use Celebron\social\old\interfaces\RequestIdInterface;
use Celebron\social\old\interfaces\GetUrlsInterface;
use Celebron\social\old\interfaces\GetUrlsTrait;
use Celebron\social\old\interfaces\ToWidgetInterface;
use Celebron\social\old\interfaces\ToWidgetTrait;
use Celebron\social\old\OAuth2Request;
use Celebron\social\old\Request;
use Celebron\social\old\RequestCode;
use Celebron\social\old\RequestId;
use Celebron\social\old\RequestToken;
use Celebron\social\old\Social;
use Celebron\social\old\SocialConfiguration;
use Celebron\social\old\WidgetSupport;
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

    public const METHOD_MAILTOKEN = 'mailToken';
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

    #[OAuth2Request]
    public function actionMailtoken():bool
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
