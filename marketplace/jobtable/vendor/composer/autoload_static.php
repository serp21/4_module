<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdf7769089645b2a80ddf131220c6563c
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\Stafftable\\' => 15,
            'App\\Core\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\Stafftable\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/classes',
        ),
        'App\\Core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/core',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdf7769089645b2a80ddf131220c6563c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdf7769089645b2a80ddf131220c6563c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitdf7769089645b2a80ddf131220c6563c::$classMap;

        }, null, ClassLoader::class);
    }
}
