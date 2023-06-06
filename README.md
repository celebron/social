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
            'class' => Celebron\social\SocialConfiguration::class,
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
Необходимо подключить компонент <i>SocialConfiguration</i> в <i>bootstrap</i>, как приведено в примере
### [[SocialConfiguration::class]]
    [optional] string       $route ('social')   - роут для OAuth redirect path   
    [optional] Closure|null $onError (null)     - обработка всех ошибок socials
    [optional] Closure|null $onSuccess (null)   - обработчик всех успешных выполнений (event)
    [optional] Closure|null $onFailed (null)    - обработчик всех провальных выполнений (event)
    [required] Social[]     $socials            - список всех соц. сетей ([ 'ключ" => AuthBase::class ])
   

### [[OAuth2::class]]    (Google::class, Yandex::class, ...) 
    [optional] bool   $activate (false) - активировать механизм
    [optional] string $name             - название для Widget
    [optional] $icon                    - иконка для Widget 
    [optional] $visible                 - отображение для Widget
    [required|optional] $clientId       - OAuth clientId
    [required|optional] $clientSecret   - OAuth clientSecret
    [optional] $clientUrl               - OAuth api url
    
    
Ссылка redirect в консолях соц.сетей (oauth2 и прочее)
-------------

    https://сайт.ru/social/<social> 
    
    <social> - название социальной сети (google, yandex и т.п.). Индекс массива $socials [[SocialConfiguration]]
