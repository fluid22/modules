<?php

namespace Fluid22\Module\Models;

class Options extends Model
{
    protected string $prefix = 'fluid22_';

    /**
     * Construct model
     */
    public function __construct() {
        $this->hydrate();
    }

    /**
     * Hydrate options from database
     */
    public function hydrate() {
        foreach ( $this->data as $name => $value ) {
            $this->data[ $name ] = get_option( $this->prefix . $name );
        }
    }

    /**
     * Save changes to options
     */
    public function save() {
        foreach ( $this->changed as $key ) {
            update_option( $this->prefix . $key, $this->data[ $key ], false );
        }
    }
}