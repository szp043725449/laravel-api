<?php
return[

    /*
    |--------------------------------------------------------------------------
    | Configure Path
    |--------------------------------------------------------------------------
    |
    | 配置接口请求和返回参数的文件存储目录
    */
    'configure_path' => ''//app_path('configure'),

    /*
    |--------------------------------------------------------------------------
    | signOperationClass
    |--------------------------------------------------------------------------
    |
    | 配置签名操作类
    */
    'signOperationClass' => ''//App\Services\SignOperation::class,

    /*
    |--------------------------------------------------------------------------
    | signMessageClass
    |--------------------------------------------------------------------------
    |
    | 配置签名信息类
    */
    'signMessageClass' => ''//App\Services\SignMessage::class,

    /*
    |--------------------------------------------------------------------------
    | signMessageClass
    |--------------------------------------------------------------------------
    |
    | 配置权限认证类
    */
    'authClass' => App\Services\Authentication::class,
];