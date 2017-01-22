# Laravelextends/requestapi

## Documentation

## 安装

Require this package  

```php
php composer.phpar require "laravelextends/requestapi:v1.0.1.x-dev"
```

After adding the package, add the ServiceProvider to the providers array in `config/app.php`

```php
Integration\Api\IntegrationServiceProvider::class,
```


To publish the config use:

```php
php artisan vendor:publish
```

配置config

```php
return[

    /*
    |--------------------------------------------------------------------------
    | Configure Path
    |--------------------------------------------------------------------------
    |
    | 配置接口请求和返回参数的文件存储目录
    */
    'configure_path' => false,//app_path('configure'),

    /*
    |--------------------------------------------------------------------------
    | signOperationClass
    |--------------------------------------------------------------------------
    |
    | 配置签名操作类
    */
    'signOperationClass' => '',//App\Services\SignOperation::class,

    /*
    |--------------------------------------------------------------------------
    | signMessageClass
    |--------------------------------------------------------------------------
    |
    | 配置签名信息类
    */
    'signMessageClass' => App\Services\SignMessage::class,

    /*
    |--------------------------------------------------------------------------
    | signMessageClass
    |--------------------------------------------------------------------------
    |
    | 配置权限认证类
    */
    'authClass' => '',//App\Services\Authentication::class,


    /*
    |--------------------------------------------------------------------------
    | errorClass
    |--------------------------------------------------------------------------
    |
    | 配置错误处理类
    */
    'errorClass' => '',//App\Services\ErrorResponse::class,

    /*
    |--------------------------------------------------------------------------
    | cacheNameClass
    |--------------------------------------------------------------------------
    |
    | 配置缓存名字类
    */
    'cacheNameClass' => '',//App\Services\ApiCacheName::class,
];
```

新建签名信息类(App\Services\SignMessage)
```php
<?php

namespace App\Services;

use Illuminate\Http\Request;

class SignMessage implements \Integration\Api\Services\SignMessage
{
    private $request;

    /**
     * 构造方法已实现依赖注入
     * SignMessage constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 获得签名密钥
     * @return string
     */
    public function getSecret()
    {
        return "sdfsdfdf";
    }

    /**
     * 获得接口地址生效时间
     * @return int
     */
    public function timeSpan()
    {
        return 60;
    }

    /**
     * 获得接口请求时间
     * @return mixed
     */
    public function happened()
    {
        return $this->request->request->get('requestTime');
    }

    /**
     * 获得要签名的数据
     * @return array
     */
    public function getSignData()
    {
        $data = $this->request->request->all();
        unset($data['sign']);

        return $data;
    }

    /**
     * 获得用户传入数据
     * @return mixed
     */
    public function getUserSignResult()
    {
        return $this->request->request->get('sign');
    }


}
```

新建签名验证类(App\Services\SignOperation)

```php
namespace App\Services;

use Integration\Api\Services\DefaultSignAppend;
use Integration\Api\Services\ErrorMessage;
use Integration\Api\Services\Md5Sign;
use Integration\Api\Services\SignAppend;
use Integration\Api\Services\SignatureOperation;
use Integration\Api\Services\SignMessage;
use Integration\Api\Services\SuccessMessage;

class SignOperation implements SignatureOperation
{
    /**
     * @param SignMessage $signMessage
     * @return \Integration\Api\Services\Message;
     */
    public static function signature(SignMessage $signMessage)
    {
        $signAppend = new DefaultSignAppend(SignAppend::SIGN_STRING_SUFFIX, $signMessage->getSecret().$signMessage->happened());
        $md5Sign = new Md5Sign($signMessage->getSignData(), $signAppend, Md5Sign::DATA_ASC);
        $time = time();
        if (Md5Sign::verify($signMessage->getUserSignResult(), $md5Sign)) {

            if ($signMessage->happened() >= $time - $signMessage->timeSpan() && $signMessage->happened()<= $time+$signMessage->timeSpan()) {
                return new SuccessMessage();
            }
        }

        return new ErrorMessage('sign error', '22222');
    }
}
```

新建权限控制类(App\Services\Authentication)
```php
namespace App\Services;

use Integration\Api\Configure\Configure;
use Integration\Api\Services\ErrorMessage;
use Integration\Api\Services\SuccessMessage;

class Authentication implements \Integration\Api\Services\Authentication
{

    /**
     * 已实现依赖注入
     * Authentication constructor.
     * @param Configure $iconfigure
     */
    public function __construct(Configure $iconfigure)
    {
        dd($iconfigure->getSignMessage());

    }

    /**
     * 权限自定义验证方法,通过返回Integration\Api\Services\SuccessMessage
     * @param $power
     * @return ErrorMessage
     */
    public function auth($power)
    {
        return new ErrorMessage("sdfsdf", "sdfdsf");
    }

}
```

配置控制器
```php
    /**
     * @Integration(configure="user.login", power="sdfd", cache={"caching_time":0.5, "cache_name"="ssdf"})
     */
```

```php
    /**
     * @Integration(configure="user.login", power="sdfd", cache={"caching_time":0.5, "cache_name"=":abc();"})
     */
```

```php
    /**
     * @Integration(configure="user.login", power="sdfd", cache={"caching_time":0.5, "cache_name"="!function(){};"})
     */
```

```php
    /**
     * @Get("/abc", as="abcd")
     * @Integration(configure="user.login", power="admin", cache={"caching_time":0.1, "cache_name"="@getDefaultCacheName"})
     */
```

```php
    /**
     * @Get("/abc", as="abcd")
     * @Integration(configure="user.login", power="admin", cache={"caching_time":0.1, "cache_name"="@getDefaultCacheName"})
     */
    public function index(Configure $iconfigure)
    {
        $paramter = $iconfigure->attachedValue();
        dump($iconfigure->getFirstDisposed());
        return new Response("sdfdssdfsdff");
    }
```

生成配置
```php
php artisan integration:annotaion:create
```

新建配置文件
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