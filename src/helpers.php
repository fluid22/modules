<?php

namespace Fluid22\Module;

use League\Container\Container;
use League\Container\ReflectionContainer;

/**
 * Set up application container instance
 *
 * @return void
 */
function setup_container() {
    $GLOBALS['fluid22_container'] = new Container();

    if ( apply_filters( 'fluid22_container_autowire', true ) ) {
        $GLOBALS['fluid22_container']->delegate( new ReflectionContainer() );
    }
}

/**
 * Get the application container instance
 *
 * @return Container
 */
function container() {
    if (
        ! isset( $GLOBALS['fluid22_container'] ) ||
        ! is_a( $GLOBALS['fluid22_container'], Container::class )
    ) {
        setup_container();
    }

    return $GLOBALS['fluid22_container'];
}

/**
 * Set up application modules
 *
 * @param string[] $modules
 */
function start( array $modules ) {
    foreach ( $modules as $module_name ) {
        container()->add( $module_name );
    }

    foreach ( $modules as $module_name ) {
        $module = container()->get( $module_name );

        if ( is_a( $module, Module::class ) ) {
            $module->setup();
        }
    }
}