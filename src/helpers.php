<?php

namespace Fluid22\Module;

use League\Container\Container;

/**
 * Get the application container instance
 *
 * @return Container
 */
function container() {
    if ( ! is_a( $GLOBALS['fluid22_container'], Container::class ) ) {
        $GLOBALS['fluid22_container'] = new Container();
    }

    return $GLOBALS['fluid22_container'];
}