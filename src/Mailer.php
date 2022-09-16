<?php

namespace yzh52521\mailer;

/**
 *
 * @see \yzh52521\mailer\mail\Mailer
 * @mixin \yzh52521\mailer\mail\Mailer
 */
class Mailer
{
    public static $_instance = null;

    public static function instance()
    {
        if ( !static::$_instance ) {
            static::$_instance = new \yzh52521\mailer\mail\Mailer();
        }
        return static::$_instance;
    }


    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(...$arguments);
    }
}