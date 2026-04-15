<?php

namespace Fluid22\Module;

use League\Container\Container;
use League\Container\ReflectionContainer;

/**
 * Key used when a caller does not supply one. Each plugin using this library
 * should pass its own key (e.g. its text domain) to keep containers isolated.
 */
const DEFAULT_CONTAINER_KEY = 'default';

/**
 * Create a container bucket under $key.
 *
 * @param string|null $key Container key. Null uses the default bucket.
 * @return void
 */
function setup_container( ?string $key = null ) {
    $key = $key ?? DEFAULT_CONTAINER_KEY;

    $container = new Container();

    if ( apply_filters( 'fluid22_container_autowire', true, $key ) ) {
        $container->delegate( new ReflectionContainer() );
    }

    if ( ! isset( $GLOBALS['fluid22_containers'] ) || ! is_array( $GLOBALS['fluid22_containers'] ) ) {
        $GLOBALS['fluid22_containers'] = array();
    }

    $GLOBALS['fluid22_containers'][ $key ] = $container;

    // Back-compat alias: legacy code read the default container from this key.
    if ( $key === DEFAULT_CONTAINER_KEY ) {
        $GLOBALS['fluid22_container'] = $container;
    }
}

/**
 * Get a container by key, creating it on first access.
 *
 * @param string|null $key Container key. Null uses the default bucket.
 * @return Container
 */
function container( ?string $key = null ) {
    $key = $key ?? DEFAULT_CONTAINER_KEY;

    if (
        ! isset( $GLOBALS['fluid22_containers'][ $key ] ) ||
        ! is_a( $GLOBALS['fluid22_containers'][ $key ], Container::class )
    ) {
        setup_container( $key );
    }

    return $GLOBALS['fluid22_containers'][ $key ];
}

/**
 * Register and boot a set of modules against a single container bucket.
 *
 * Fires `fluid22_modules_booted` after every module's `setup()` has run, with
 * the resolved container key and the list of module class names.
 *
 * @param string[]    $modules Fully qualified class names.
 * @param string|null $key     Container key. Null uses the default bucket.
 * @return void
 */
function start( array $modules, ?string $key = null ) {
    $resolved_key = $key ?? DEFAULT_CONTAINER_KEY;
    $container    = container( $resolved_key );

    foreach ( $modules as $module_name ) {
        $container->add( $module_name );
    }

    foreach ( $modules as $module_name ) {
        $module = $container->get( $module_name );

        if ( is_a( $module, Module::class ) ) {
            $module->setup();
        }
    }

    do_action( 'fluid22_modules_booted', $resolved_key, $modules );
}
