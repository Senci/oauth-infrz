<?php

namespace Infrz\OAuth\Control;

class Autoloader
{
    private static $packages = array();

    /**
     * Loads a class by its classname
     *
     * @param string $classname
     */
    public static function loadClass($classname)
    {
        if (is_file(self::$packages[$classname])) {
            require_once(self::$packages[$classname]);
        }
    }

    /**
     * Adds a class to Autoloader
     *
     * @param string $className Name of the class
     * @param string $filePath Path to the file containing the class
     */
    public static function addClass($className, $filePath)
    {
        self::$packages[$className] = $filePath;
        echo 'Added '.$filePath.' to className '.$className.'<br/>';
    }

    /**
     * Adds a package to Autoloader
     *
     * @param array $package An array of classes key=className, value=filePath
     */
    public static function addPackage($package)
    {
        self::$packages = array_merge(self::$packages, $package);
    }

    public static function addPath($path, $recursive = false, $namespacePrefix = null)
    {
        $dir = opendir($path);
        while ($file = readdir($dir)) {
            $filePath = sprintf('%s/%s', $path, $file);
            $isPhpFile = preg_match('/[-a-zA-Z0-9_]+.php/', $file);
            if (is_file($filePath) and $isPhpFile) {
                $className = str_replace('/', '\\', $filePath);
                $className = substr($className, 0, -4); // remove '.php' from the end
                if ($namespacePrefix) {
                    $className = sprintf('%s\\%s', $namespacePrefix, $className);
                }
                self::addClass($className, $filePath);
            } elseif ($recursive and is_dir($filePath) and ($file != '.') and ($file != '..')) {
                self::addPath($filePath);
            }
        }
        closedir($dir);
    }

    /**
     * Adds a default package
     */
    public static function addDefault()
    {
        self::addPath('Control', true, 'Infrz\\OAuth');
        self::addPath('Model', true, 'Infrz\\OAuth');
        self::addClass('Infrz\\OAuth\\ResponseBuilder', 'ResponseBuilder.php');
    }
}

function __autoload($classname)
{
    if (preg_match('/Twig/', $classname)) {
        echo $classname.'<br/>';
    }
    AutoLoader::loadClass($classname);
}

spl_autoload_register('Infrz\OAuth\Control\__autoload');

Autoloader::addDefault();
