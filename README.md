# Laravelextends/requestapi

## Documentation

## Installation

Require this package  

```php
php composer.phpar require "laravelextends/requestapi:dev-master"
```

After adding the package, add the ServiceProvider to the providers array in `config/app.php`

```php
Integration\Api\IntegrationServiceProvider::class,
```


To publish the config use:

```php
php artisan vendor:publish --tag="config"
```

```php
php artisan integration:annotaion:create
```

```php
    /**
     * @Integration(configure="user.login", power="sdfd", cache={"caching_time":0.5, "cache_name"="ssdf"})
     */
```

```php
User/abc.php

return [
    'parent' => ['user.abc', 'user.sdf'],//继承的配置文件

    "responseType" => "json", //html|json

    "requestParamters" => [

        "token" => [//因为BaseConfig有token所以会继承,如果不写则默认在account_type 后添加
        ],
        "account_type" => [
            "validate"=>[
                'rules' => "required",
                'message'=> ["account_type.required" => ['code'=>'100001', 'message'=>'参数账号类型必填']],
                'validate_function'=>function(){
                    return new SuccessMessage();

                },

            ],
            'attached_value' => [
                "realParamterName" => "s",
                "value" => function(Request $request,
                                    Configure $iconfigure){
                    $accountType = $request->get('account_type');

                    return $accountType;
                }

            ]

        ]
    ],
];
```