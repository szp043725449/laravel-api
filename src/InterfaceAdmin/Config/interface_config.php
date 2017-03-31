<?php
return [
    /**
     * 默认action代码
     */
    'defaultActionCode'=> "",

    /**
     * annotate 排序
     */
    'sortAnnotate'=> null,

    /**
     * reg
     */
    'reg' => [
        'Post' => [
            'format' => '@%s("%s", as="%s")',
            'args' => ['@request', '@routeAddress', '@routeName'],
        ],
        'api' => [
            'format' => '@api {%s} %s %s',
            'args' => ['@api'],
        ],
        'apiName' => [
            'format' => '@apiName %s',
            'args' => ['@apiName'],
        ],
        'apiGroup' => [
            'format' => '@apiGroup %s',
            'args' => ['@apiGroup'],
        ],
        'apiParam' => [
            'format' => '@apiParam {%s} %s %s',
            'args' => '@apiParam',
        ],
        'apiSuccess' => [
            'format' => '@apiSuccess {%s} %s %s',
            'args' => '@apiSuccess',
        ],
    ],
];