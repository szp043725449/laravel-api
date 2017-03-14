<?php

namespace Integration\BladeExtends\Directives;

use Integration\BladeExtends\Contracts\Directive;

class ForeachDirective extends Directive
{
    public function foreachTag($bladeString, $app, $pattern, $replacement, $blade)
    {
        return preg_replace($pattern, $replacement, $bladeString);
    }

    public function endforeachTag($bladeString, $app, $pattern, $replacement, $blade)
    {
        return preg_replace($pattern, $replacement, $bladeString);

    }

}