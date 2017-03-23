<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/3/13
 * Time: 下午4:57
 */

namespace Integration\BladeExtends\Directives;


use Integration\BladeExtends\Contracts\Directive;

class DataSourceDirective extends Directive
{
    public function DataSourceTag($bladeString, $app, $pattern, $replacement, $blade)
    {
        return preg_replace($pattern, $replacement, $bladeString);
    }

    public function endDataSourceTag($bladeString, $app, $pattern, $replacement, $blade)
    {
        return preg_replace($pattern, $replacement, $bladeString);
    }
}