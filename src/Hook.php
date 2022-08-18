<?php

namespace Fluid22\Module;

abstract class Hook
{
    public static array $hooks = array();
    public static int $priority = 10;
    public static int $arguments = 1;

    abstract public function run(...$args);
}