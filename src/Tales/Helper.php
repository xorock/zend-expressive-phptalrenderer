<?php

namespace Zend\Expressive\Phptal\Tales;

use PHPTAL_Tales;
use PHPTAL_Php_Transformer;

class Helper implements PHPTAL_Tales
{
    /**
     * Tal extension to allow helper invoke.
     *
     * Example use within template: <a tal:attributes="href helper:url('home')">Link</a>
     * 
     * @param string $src     The original template string.
     * @param bool   $nothrow Whether to throw an exception on error.
     * @return mixed
     */
    public static function helper($src, $nothrow)
    {
        $src = 'helper->' . trim($src);
        return PHPTAL_Php_Transformer::transform($src, '$ctx->');
    }
}
