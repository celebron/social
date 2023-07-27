<?php

namespace Celebron\socialSource\widgets;

use Celebron\socialSource\Configuration;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\Request;
use Celebron\socialSource\Response;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

/**
 *
 * @property-read Configuration $configure
 * @property-read array $socials
 */
class Login extends Widget
{
    //array - список отображаемых
    //string - конкретный отображаемый
    //false - управление через Configuration
    public false|array $names = false;
    public string $componentName = 'social';
    public string $render = ViewerInterface::VIEW_LOGIN;

    /**
     * @throws InvalidConfigException
     */
    public function getConfigure (): Configuration
    {
        return \Yii::$app->get($this->componentName);
    }

    /**
     * @return array|Request[]
     * @throws InvalidConfigException|\ReflectionException
     */
    public function getSocials (): array
    {
        $socials = $this->getConfigure()->getSocials(ViewerInterface::class);
        if ($this->names === false) {
            return $socials;
        }

        return ArrayHelper::filter($socials, $this->names);
    }

    /**
     * @throws InvalidConfigException|\ReflectionException
     */
    public function run ()
    {
        return $this->render($this->render, [
                'configure' => $this->getConfigure(),
                'socials' => $this->getSocials()]
        );
    }

}