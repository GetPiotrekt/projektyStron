<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitdb6dc7038512ff66de09f5d3d810efba
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInitdb6dc7038512ff66de09f5d3d810efba', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitdb6dc7038512ff66de09f5d3d810efba', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitdb6dc7038512ff66de09f5d3d810efba::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}