<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Celebron Social Extension for Yii 2</h1>
    <br/>
</p>

This extension provides the HTTP client for the [Yii framework 2.0](http://www.yiiframework.com).


Installation
------------


Configuration
-------------
Редактируем файл `config/web.php`, пример:

```php
    ...,
    'bootstrap' => [..., 'social' ],
    'components'=>[
        'social' => [
            'class' => Celebron\social\Configuration::class,
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
Необходимо подключить компонент <i>Configuration::class</i> в <i>bootstrap</i>, как приведено в примере
### [[Configuration::class]]
    [optional] string       $route ('social')   - роут для OAuth redirect path   
    [optional] string|null  $paramsGroup (null) - ключ массива с настройками в \Yii::$app->params (null - не использовать)
    [optional] Closure|null $onError (null)     - обработка всех ошибок socials
    [optional] Closure|null $onSuccess (null)   - обработчик всех успешных выполнений (event)
    [optional] Closure|null $onFailed (null)    - обработчик всех провальных выполнений (event)
    [required] Social[]     $socials            - список всех соц. сетей ([ 'social" => AuthBase::class ])
   
В массиве `$socials` ключ можно опускать, тогда при регистрации ключом будет имя класса или атрибут класса SocialName  

Если указан `$paramsGroup` - тогда можно в настройках socials опускать `$clientId` и `$clientSecret` 

### [[OAuth2::class]]    (Google::class, Yandex::class, ...) 
    [optional] bool   $activate (false)      - активировать механизм
    [optional] string $name                  - название для Widget
    [optional] $icon                         - иконка для Widget 
    [optional] $visible                      - отображение для Widget
    [required|optional] $clientId (null)     - OAuth clientId
    [required|optional] $clientSecret (null) - OAuth clientSecret
    [optional] $clientUrl                    - OAuth api url
    
Если `$clientId` и `$clientSecret` null, то будут использоваться параметры 
``[{paramsGroup}][{social}]['clientId']`` и ``[{paramsGroup}][{social}]['clientSecret']`` соответственно. 
В противном случае будет вызвано исключение.

    
Ссылка redirect в консолях соц.сетей (oauth2 и прочее)
-------------

    https://сайт.ru/{route}/{social} 


Легенда
------------
    {social}      - название социальной сети (google, yandex и т.п.). Индекс массива $socials [[SocialConfiguration]]
    {route}       - Настройка в классе Configuration
    {paramsGroup} - Настройка в классе Configuration