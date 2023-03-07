<?php

namespace Xrpl\XummForWoocommerce\Tests\Helper\Mock;

class WordpressMock
{
    public static function create() : void
    {
        \WP_Mock::userFunction( 'get_transient', [
            'return' => []
        ]);

        \WP_Mock::userFunction( 'set_transient', [
            'args' => [\WP_Mock\Functions::type('string'), \WP_Mock\Functions::type('array')]
        ]);
    }
}
