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
Edit the file `config/web.php` with real data, for example:
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
                     'clientSecret' => '...',
                     'field' => 'id_yandex',
                ],
                ...    
            ],  
        ],
    ],
...
```

### [[SocialConfiguration::class]]
    [optional] string       $route ('social')            - роут для OAuth redirect path   
    [optional] string       $register ('register')       - state - регистрации
    [optional] int          $duration (0)                - Срок действия авторизации
    [optional] Closure|null $onAllError (null)           - обработка всех ошибок socials
    [optional] Closure|null $onAllRegisterSuccess (null) - обработчик всех успешных регистраций
    [optional] Closure|null $onAllLoginSuccess (null)    - обработчик всех успешных логинов
    [optional] Closure|null $onAllDeleteSuccess (null)   - обработчик всех упешных удалений
    [optional] Closure|null $findUserAlg (null)          - переопределение алгоритма поиска пользователя
    [required] Social[]     $socials                     - список всех соц. сетей 

### [[SocialOAuth::class]]    (Google::class, Yandex::class, ...)
    [required] string $field               - поле в базе данных
    [optional] bool   $activate (false)    - активировать механизм
    [optional] string $name                - название для Widget
    [optional] $icon                       - иконка для Widget 
    [required|optional] $clientId          - OAuth clientId
    [required|optional] $clientSecret      - OAuth clientSecret
    [optional] $clientUrl                  - OAuth api url
    
    
Ссылка redirect в консолях соц.сетей (oauth2 и прочее)
-------------

    https://сайт.ru/social/<social> 
    
    <social> - название социальной сети (google, yandex и т.п.). Индекс массива $socials [[SocialConfiguration]]
