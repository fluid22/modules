<?php

namespace Fluid22\Module;

use League\Container\Container;

/**
 * Get the application container instance
 *
 * @return Container
 */
function container() {
    if ( ! isset( $_GLOBALS['fluid22_container'] ) ) {
        $_GLOBALS['fluid22_container'] = new Container();
    }

    return $_GLOBALS['fluid22_container'];
}