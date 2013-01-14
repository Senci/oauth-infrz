<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

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
        $filePath = isset(self::$packages[$classname]) ? self::$packages[$classname] : false;
        if (is_file($filePath)) {
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

    /**
     * Adds a new directory (and its subdirectories) to the autoloader.
     * All php Files matching the regular expression '/[-a-zA-Z0-9_]+.php/' are added.
     *
     * @param string $path
     * @param bool $recursive when true subdirectories are added as well.
     * @param null $namespacePrefix Prefix to the Namespace.
     */
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
                self::addPath($filePath, $recursive, $namespacePrefix);
            }
        }
        closedir($dir);
    }

    /**
     * Adds a default package
     */
    public static function addDefault()
    {
        self::addPath('Control', true, 'Infrz\OAuth');
        self::addPath('Model', true, 'Infrz\OAuth');
        self::addPath('View', true, 'Infrz\OAuth');
        self::addClass('Infrz\OAuth\ResponseBuilder', 'ResponseBuilder.php');
    }
}

function __autoload($classname)
{
    AutoLoader::loadClass($classname);
}

spl_autoload_register('Infrz\OAuth\Control\__autoload');

Autoloader::addDefault();
