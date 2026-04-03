<?php

namespace Fluid22\Module\Models;

class Options
{
    protected string $prefix = 'fluid22_';

    /**
     * Passed to update_option() as the autoload flag.
     */
    protected bool $autoload = false;

    /**
     * In-memory values (logical keys, no prefix).
     *
     * @var array<string, mixed>
     */
    protected array $data = array();

    /**
     * Keys to persist with update_option on save().
     *
     * @var list<string>
     */
    protected array $changed = array();

    /**
     * Keys to remove with delete_option on save() (set via remove() / __unset).
     *
     * @var array<string, true>
     */
    protected array $deleted = array();

    /**
     * @param string|null $prefix  Override option key prefix; null keeps the class default (or subclass override).
     * @param bool|null   $autoload Override update_option autoload; null keeps the class default.
     */
    public function __construct( ?string $prefix = null, ?bool $autoload = null ) {
        if ( $prefix !== null ) {
            $this->prefix = $prefix;
        }
        if ( $autoload !== null ) {
            $this->autoload = $autoload;
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get( $name ) {
        if ( ! isset( $this->data[ $name ] ) || is_null( $this->data[ $name ] ) ) {
            $this->data[ $name ] = get_option( $this->prefix . $name );
        }

        return $this->data[ $name ];
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set( $name, $value ) {
        unset( $this->deleted[ $name ] );

        $this->data[ $name ] = $value;

        if ( ! in_array( $name, $this->changed, true ) ) {
            $this->changed[] = $name;
        }
    }

    /**
     * Drop a key from memory and schedule delete_option on save().
     */
    public function remove( string $name ): void {
        unset( $this->data[ $name ] );
        $this->deleted[ $name ] = true;
        $this->changed = array_values(
            array_filter(
                $this->changed,
                static function ( $key ) use ( $name ) {
                    return $key !== $name;
                }
            )
        );
    }

    /**
     * @param string $name
     */
    public function __unset( $name ) {
        $this->remove( $name );
    }

    /**
     * Persist dirty keys and pending deletes, then reset tracking.
     */
    public function save(): void {
        foreach ( $this->changed as $key ) {
            update_option( $this->prefix . $key, $this->data[ $key ], $this->autoload );
        }
        $this->changed = array();

        foreach ( array_keys( $this->deleted ) as $key ) {
            delete_option( $this->prefix . $key );
        }
        $this->deleted = array();
    }
}
