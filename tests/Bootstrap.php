<?php
/**
 * This source file is part of Xloit project.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * <http://www.opensource.org/licenses/mit-license.php>
 * If you did not receive a copy of the license and are unable to obtain it through the world-wide-web,
 * please send an email to <license@xloit.com> so we can send you a copy immediately.
 *
 * @license   MIT
 * @link      http://xloit.com
 * @copyright Copyright (c) 2016, Xloit. All rights reserved.
 */

/**
 * A {@link \Bootstrap} class
 */
class Bootstrap
{
    /**
     *
     *
     * @var Bootstrap
     */
    protected static $loader;

    /**
     *
     *
     * @var array
     */
    protected $namespaces = [];

    /**
     *
     *
     * @var array
     */
    protected $classMap = [];

    /**
     *
     *
     * @return array
     */
    public function getClassMap()
    {
        return $this->classMap;
    }

    /**
     * Unregister this instance as an autoloader.
     *
     * @return void
     */
    public function unregister()
    {
        spl_autoload_unregister(
            [
                $this,
                'loadClass'
            ]
        );
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class
     *
     * @return boolean
     */
    public function loadClass($class)
    {
        $file = $this->findFile($class);

        if ($file) {
            /** @noinspection PhpIncludeInspection */
            include $file;

            return true;
        }

        return false;
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class
     *
     * @return string|false
     */
    public function findFile($class)
    {
        if (strpos('\\', $class) === 0) {
            $class = substr($class, 1);
        }

        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        $file = $this->findFileWithExtension($class, '.php');

        if ($file === null) {
            // Remember that this class does not exist.
            $this->classMap[$class] = false;

            return false;
        }

        return $file;
    }

    /**
     *
     *
     * @param string $class
     * @param string $ext
     *
     * @return string
     */
    protected function findFileWithExtension($class, $ext)
    {
        // PSR-4 lookup
        $logicalPathPsr4 = str_replace($class, '\\', DIRECTORY_SEPARATOR) . $ext;

        $first = $class[0];
        $pos   = strrpos($class, '\\');

        // PSR-0 lookup
        if (false !== $pos) {
            // namespaced class name
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
                               . str_replace(substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);
        } else {
            // PEAR-like class name
            $logicalPathPsr0 = str_replace($class, '_', DIRECTORY_SEPARATOR) . $ext;
        }

        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (isset($this->namespaces[$first])) {
            foreach ($this->namespaces[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
                            return $file;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     *
     *
     * @throws \RuntimeException
     * @return self
     */
    public static function init()
    {
        return static::initAutoloader();
    }

    /**
     *
     *
     * @throws \RuntimeException
     * @return self
     */
    protected static function initAutoloader()
    {
        if (null === self::$loader) {
            self::$loader = new self();
        }

        $loader = self::$loader;

        if (file_exists(__DIR__ . '/autoload_namespaces.php')) {
            /** @var array $namespaces */
            $namespaces = require __DIR__ . '/autoload_namespaces.php';

            if (is_array($namespaces)) {
                foreach ($namespaces as $namespace => $path) {
                    $loader->set($namespace, $path);
                }
            }
        }

        if (file_exists(__DIR__ . '/autoload_classmap.php')) {
            $classMap = require __DIR__ . '/autoload_classmap.php';

            if (is_array($classMap)) {
                $loader->addClassMap($classMap);
            }
        }

        $loader->register(true);

        return $loader;
    }

    /**
     * Registers a set of namespaces directories for a given prefix, replacing any others previously set for this
     * prefix.
     *
     * @param string       $prefix
     * @param array|string $paths
     *
     * @return self
     */
    public function set($prefix, $paths)
    {
        $this->namespaces[$prefix[0]][$prefix] = (array) $paths;
    }

    /**
     *
     *
     * @param array $classMap
     *
     * @return self
     */
    public function addClassMap(array $classMap)
    {
        $this->classMap = $this->classMap ? array_merge($this->classMap, $classMap) : $classMap;

        return $this;
    }

    /**
     * Register this instance as an autoloader.
     *
     * @param bool $prepend
     *
     * @return void
     */
    public function register($prepend = false)
    {
        spl_autoload_register(
            [
                $this,
                'loadClass'
            ],
            true,
            $prepend
        );
    }
}
