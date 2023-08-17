<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\widgets\social;

use Celebron\source\social\Configuration;
use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\Social;
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
    public bool $useIcon = true;
    public string $render = ViewerInterface::VIEW_LOGIN;

    public function init ()
    {
        parent::init();
        $asset = SocialAsset::register($this->view);
        \Yii::setAlias('@public', "@web$asset->baseUrl");
    }

    /**
     * @throws InvalidConfigException
     */
    public function getConfigure (): Configuration
    {
        return \Yii::$app->get($this->componentName);
    }

    /**
     * @return array|Social[]
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
                'socials' => $this->getSocials()
            ]
        );
    }

}