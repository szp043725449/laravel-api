<?php
return [
    /*
    |--------------------------------------------------------------------------
    | directive
    |--------------------------------------------------------------------------
    |
    | 配置自定义directive
    */
    'directive'=>[
        'foreach'     => [
            'pattern'     => '/(?<!\w)(\s*)@foreach(?:\s*)\(((?:.|\n)*?)(?:\sas)((?:.|\n)*?)\)/',
            'replacement' => <<<'EOT'
$1<?php
if (isset($2) && (is_array($2) ||  $2 instanceof \iterator) ) :
    $__currentLoopData = $2; $__env->addLoop($__currentLoopData);
    foreach($__currentLoopData as $3):
    $__env->incrementLoopIndices(); $loop = $__env->getFirstLoop();
?>
EOT
        ],
        'endforeach'  => [
            'pattern'     => '/(?<!\\w)(\\s*)@endforeach(\\s*)/',
            'replacement' => <<<'EOT'
$1<?php
endforeach;
$__env->popLoop(); 
$loop = $__env->getFirstLoop();
endif;
?>$2
EOT
        ],
        'dataSource' => [
            'pattern'     => '/(?<!\w)(\s*)@dataSource(?:\s*)\{@((?:.|\n)*?)@\}/',
            'replacement' => <<<'EOT'
$1<?php
$dataSource = \Integration\BladeExtends\DataSource::init('$2');
if ($dataSource instanceof \Integration\BladeExtends\DataSource) :
    $__currentLoopData = $dataSource->getIterator(); $__env->addLoop($__currentLoopData);
    $__key = $dataSource->getKey();
    $__value = $dataSource->getValue();
    foreach($__currentLoopData as $$__key=>$$__value):
    $__env->incrementLoopIndices(); $loop = $__env->getFirstLoop();
?>
EOT
        ],
        'endDataSource'  => [
            'pattern'     => '/(?<!\\w)(\\s*)@endDataSource(\\s*)/',
            'replacement' => <<<'EOT'
$1<?php
endforeach;
$__env->popLoop(); 
$loop = $__env->getFirstLoop();
endif;
?>$2
EOT
        ],
        'define'  => [
            'pattern'     => '/(?<!\\w)(\\s*)@define(.+)(\\s*)/',
            'replacement' => <<<'EOT'
$1<?php ${2}; ?>$3
EOT
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | directiveExtendsClass
    |--------------------------------------------------------------------------
    |
    | 扩展类
    */
    'directiveExtendsClass' => [
        \Integration\BladeExtends\Directives\ForeachDirective::class,
        \Integration\BladeExtends\Directives\DataSourceDirective::class,
        \Integration\BladeExtends\Directives\DefineDirective::class,
    ]
];