<?php

namespace Fluid22\Module\Models;

abstract class Model
{
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
        return $this->data[ $name ];
    }

    /**
     * Set value for data store
     *
     * @param $name
     * @param $value
     * @return void
     */
    public function __set( $name, $value ) {
        $this->data[ $name ] = $value;

        if ( ! in_array( $name, $this->changed ) ) {
            $this->changed[] = $name;
        }
    }

    /**
     * Hydrate model
     */
    abstract public function hydrate();

    /**
     * Save changes to options
     */
    abstract public function save();
}