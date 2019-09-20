<?php

namespace Setcooki\Wp\Api\Retry\Traits;

/**
 * Trait Singleton
 * @package Setcooki\Wp\Api\Retry\Traits
 */
trait Singleton
{
    /**
     * @var null|$this
     */
    protected static $_instance = null;


    /**
     * Create/retrieve instance
     *
     * @param mixed ...$args
     * @return $this
     */
    final public static function getInstance(...$args)
    {
        if(static::$_instance === null)
        {
            static::$_instance = new static($args);
        }
        return static::$_instance;
    }


    /**
     * check if singleton has been created already
     *
     * @return bool
     */
    final public static function hasInstance()
    {
        return (static::$_instance) ? true : false;
    }


    /**
     * Constructor is private so class the uses trait will be initialized with $this::init
     *
     * @param mixed ...$args
     */
    final private function __construct(...$args)
    {
        call_user_func_array(array($this, 'init'), ((isset($args[0]) && is_array($args[0])) ? $args[0] : $args));
    }


    /**
     * Class that uses this trait should implement this function
     *
     * @param mixed ...$args
     */
    protected function init(...$args) {}


    /**
     * deny serialization
     */
    final public function __wakeup() {}


    /**
     * deny cloning
     */
    final public function __clone() {}
}
