<?php

namespace Fluid22\Module\Models;

class Options
{
    protected string $prefix = 'fluid22_';

    /**
     * Data storage for model
     *
     * @var array
     */
    protected array $data = array();

    /**
     * Array of changed keys
     *
     * @var array
     */
    protected array $changed = array();

    /**
     * Get value from data store
     *
     * @param $name
     * @return mixed
     */
    public function __get( $name ) {
        if ( isset( $this->changed[ $name ] ) ) {
            return $this->changed[ $name ];
        }

        if ( ! isset( $this->data[ $name ] ) || is_null( $this->data[ $name ] ) ) {
            $this->data[ $name ] = get_option( $this->prefix . $name );
        }

        return $this->data[ $name ];
    }

    /**
     * Set value for data store
     *
     * @param $name
     * @param $value
     * @return mixed|void
     */
    public function __set( $name, $value ) {
        $this->data[ $name ] = $value;

        if ( ! in_array( $name, $this->changed ) ) {
            $this->changed[] = $name;
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