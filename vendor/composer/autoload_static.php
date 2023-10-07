<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd4005f34ecc7e39fa10938818ed7db9e
{
    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PHPExcel' => 
            array (
                0 => __DIR__ . '/..' . '/phpoffice/phpexcel/Classes',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInitd4005f34ecc7e39fa10938818ed7db9e::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitd4005f34ecc7e39fa10938818ed7db9e::$classMap;

        }, null, ClassLoader::class);
    }
}