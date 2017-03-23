<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/3/15
 * Time: 下午4:25
 */

namespace Integration\BladeExtends\Directives;

use Integration\BladeExtends\Contracts\Directive;

class DefineDirective extends Directive
{
    public function defineTag($bladeString, $app, $pattern, $replacement, $blade)
    {
        return preg_replace($pattern, $replacement, $bladeString);
    }
}