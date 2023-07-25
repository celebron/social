<?php /** @noinspection MissedFieldInspection */


namespace Celebron\social\socials;


use Celebron\social\attrs\WidgetSupport;
use Celebron\social\interfaces\AbstractOAuth2;
use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\interfaces\ToWidgetTrait;
use Celebron\social\RequestCode;
use Celebron\social\RequestId;
use Celebron\social\RequestToken;
use Celebron\social\SocialResponse;
use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;


/**
 *
 * @property-read string $uriToken
 * @property-read string $uriInfo
 * @property-read string $uriCode
 * @property-read string $baseUrl
 */
#[WidgetSupport(true, true)]
class Yandex extends AbstractOAuth2 implements GetUrlsInterface, ToWidgetInterface
{
    use ToWidgetTrait;

    private string $_icon = 'https://yastatic.net/s3/doc-binary/freeze/ru/id/228a1baa2a03e757cdee24712f4cc6b2e75636f2.svg';
    private ?string $_name = null;
    private bool $_visible = true;

    public ?string $fileName = null;

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function requestId (RequestId $request): SocialResponse
    {
        $login = $request->getHeaderOauth(['format'=> 'json']);
        return $this->sendResponse($login, 'id');
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

    public function getBaseUrl (): string
    {
        return "https://oauth.yandex.ru";
    }

    public function getUriCode (): string
    {
        return 'authorize';
    }

    public function getUriToken (): string
    {
        return 'token';
    }

    public function getUriInfo (): string
    {
        return 'https://login.yandex.ru/info';
    }
}
