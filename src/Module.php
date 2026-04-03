<?php

namespace Fluid22\Module;

abstract class Module
{
    /**
     * Use this method to define actions, filters, and shortcodes
     */
    abstract public function setup();

    /**
     * Render a template file under this module's templates/ directory.
     *
     * Template names must be safe path segments only (see resolve_template_path()).
     *
     * Variables from `$vars` are extracted into the template scope with EXTR_SKIP, so they
     * cannot replace internal locals. Only keys that are valid PHP variable names are used;
     * numeric or invalid keys are ignored. `$this` remains the module instance.
     *
     * Avoid reserved names `__f22_path` and `__f22_vars` in your keys (they will not overwrite
     * the internal parameters and will be skipped for extraction).
     *
     * @param string $template Relative template name without .html.php (e.g. "hero" or "emails/summary").
     * @param array<string, mixed> $vars Keys become variables in the template (extract).
     * @return string|false Rendered HTML, or false if the template is missing or invalid.
     */
    public function get_template( string $template, array $vars = array() ) {
        $path = $this->resolve_template_path( $template );
        if ( $path === null ) {
            return false;
        }

        ob_start();
        $this->render_template_file( $path, $this->sanitize_template_vars( $vars ) );
        $output = ob_get_clean();

        return $output === false ? false : $output;
    }

    /**
     * Allow only array keys that are valid PHP variable identifiers for extract().
     *
     * @param array<string, mixed> $vars
     * @return array<string, mixed>
     */
    protected function sanitize_template_vars( array $vars ): array {
        $safe = array();
        foreach ( $vars as $key => $value ) {
            if ( is_string( $key ) && preg_match( '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $key ) ) {
                $safe[ $key ] = $value;
            }
        }

        return $safe;
    }

    /**
     * Isolated scope for extract() + include: only $__f22_path, $__f22_vars, extracted keys, and $this.
     */
    protected function render_template_file( string $__f22_path, array $__f22_vars ): void {
        extract( $__f22_vars, EXTR_SKIP );
        include $__f22_path;
    }

    /**
     * Resolve a template name to an absolute path inside templates/, or null if invalid or missing.
     */
    protected function resolve_template_path( string $template ): ?string {
        $template = trim( $template, "/ \t\n\r\0\x0B" );
        if ( $template === '' || ! preg_match( '#^[a-zA-Z0-9_-]+(?:/[a-zA-Z0-9_-]+)*$#', $template ) ) {
            return null;
        }

        $dir = $this->get_dir() . '/templates';
        $full = $dir . '/' . $template . '.html.php';

        $base = realpath( $dir );
        if ( $base === false ) {
            return null;
        }

        $resolved = realpath( $full );
        if ( $resolved === false || ! is_file( $resolved ) ) {
            return null;
        }

        $base = rtrim( $base, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
        if ( strncmp( $resolved, $base, strlen( $base ) ) !== 0 ) {
            return null;
        }

        return $resolved;
    }

    /**
     * Get the directory path for the current module
     *
     * @return string
     */
    protected function get_dir() {
        return dirname( ( new \ReflectionClass( static::class ) )->getFileName() );
    }
}