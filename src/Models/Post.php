<?php

namespace Fluid22\Module\Models;

class Post extends Model
{
    /**
     * Post ID
     *
     * @var int
     */
    protected int $id = 0;

    /**
     * Post Type
     *
     * @var string
     */
    protected string $post_type = 'post';

    /**
     * Hydrate from post meta
     */
    public function hydrate() {
        // TODO: Implement hydrate() method.
    }

    /**
     * Save changes to db
     */
    public function save() {
        // TODO: Implement save() method.
    }
}