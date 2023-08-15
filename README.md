<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Celebron Social Extension for Yii 2</h1>
    <br/>
</p>

This extension provides the HTTP client for the [Yii framework 2.0](http://www.yiiframework.com).

## Installation
 composer require celebron/yii2-oauth2-social
## Configuration

__Файл `frontend/config/main.php`, пример:__

```php
    ...,
    'bootstrap' => [..., 'social' ],
    'components'=>[
        'user' => [
            'identityClass' => \common\models\User::class,
            ...
        ],
        'social' => [
            'class' => Celebron\socialSource\Configuration::class,
            'socials' => [
                 [
                     'class' => Yandex::class, //Google::class и т.д.
                     'active' => true,
                     'clientId' => '...',
                     'clientSecret' => '...,
                ],
                ...  
            ],  
        ],
    ],
    ...
```
__Необходимо:__
- подключить компонент `Configuration::class` в `bootstrap`, как приведено в примере;
- в компоненте `social` установить переменную `$socials` список всех соц. сетей по правилам `Yii::createObject()`;
- реализовать интерфейс `SocialUserInterface` и `IdentityInterface` и подключить к компоненту `user`;
    - подключить трейт `UserManagementTrait` (по необходимости).

## Configuration::class

      [optional] string       $route ('social')   - роут для OAuth redirect path
      [optional] string|null  $paramsGroup (null) - ключ массива с настройками в \Yii::$app->params (null - не использовать)
      [optional] array        $socialEvents       - массив событий ['название-события' => \Closure]
      [required] Social[]     $socials            - список всех соц. сетей (Правило формирования \Yii::createObject())
      [optional] \Closure|null $paramsHandler      - настройка $params в Social классах

- В массиве `$socials` ключ можно опускать, тогда при регистрации ключом будет имя класса.
- Если класс реализует интерфейс `CustomRequestInterface`, то ключ обязателен (выдаст исключение)
- Если `$paramsGroup` установлен, то настройки `clientId` и `clientSecret` могут браться из \Yii::$app->params[$paramsGroup][{socialName}]
- Если `$paramsHandler` установлен, то настройки `clientId` и `clientSecret` могут браться из callback-function
```php 
function($socialName):array { /** @var Configure $this */ }
```

## Классы авторизации
__OAuth2::class__ (_Google::class, Yandex::class, ..._)

namespace __Celebron\socials__

    [optional] bool     $activate (false)    - активировать механизм
    [optional] string   $name                - название для Widget
    [optional] string   $icon                - иконка для Widget
    [optional] bool     $visible             - отображение для Widget
    [required|optional] $clientId (null)     - OAuth clientId
    [required|optional] $clientSecret (null) - OAuth clientSecret

Если `$clientId` и `$clientSecret` не определены, то будут использоваться параметры
`$params['clientId']` и `$params['clientSecret']` соответственно, в противном случае будет вызвано исключение.
Зависит от настроек `Configure::$paramsGroup` и `Configure::$paramsHandler`

## Ссылка redirect в консолях соц. сетей (oauth2 и прочее)
    https://сайт.ru/{route}/{social}
## Легенда
    {social} - название социальной сети (google, yandex и т.п.). Индекс массива Сonfigutation::$socials.
    {route}  - Настройка в классе Configuration
