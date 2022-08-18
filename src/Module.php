<?php

namespace Fluid22\Module;

abstract class Module
{
    /**
     * Use this method to define actions, filters, and shortcodes
     */
    abstract public function setup();

    /**
     * Get template as a string
     *
     * @param string $template
     * @param array $args
     * @return string|false
     */
    public function get_template( string $template, $args = array() ) {
        if ( ! empty( $args ) ) {
            extract( $args );
        }

        ob_start();

        include $this->get_dir() . "/templates/{$template}.html.php";

        return ob_get_clean();
    }

    /**
     * Get the directory path for the current module
     *
     * @return string
     */
    protected function get_dir() {
        return dirname( ( new \ReflectionClass( static::class ) )->getFileName());
    }
}