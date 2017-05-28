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

namespace Xloit\Bridge\Zend\ModuleManager;

use Xloit\Bridge\Zend\ModuleManager\Feature\DirectoryProviderTrait;
use Xloit\Bridge\Zend\ModuleManager\Feature\NamespaceProviderTrait;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\ModuleManager\Feature\FilterProviderInterface;
use Zend\ModuleManager\Feature\FormElementProviderInterface;
use Zend\ModuleManager\Feature\HydratorProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\Feature\InputFilterProviderInterface;
use Zend\ModuleManager\Feature\LocatorRegisteredInterface;
use Zend\ModuleManager\Feature\LogProcessorProviderInterface;
use Zend\ModuleManager\Feature\LogWriterProviderInterface;
use Zend\ModuleManager\Feature\RouteProviderInterface;
use Zend\ModuleManager\Feature\SerializerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ValidatorProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;

/**
 * An {@link AbstractModule} abstract class.
 *
 * @abstract
 * @package Xloit\Bridge\Zend\ModuleManager
 */
class AbstractModule
    implements AutoloaderProviderInterface,
               ConfigProviderInterface,
               ControllerPluginProviderInterface,
               ControllerProviderInterface,
               InitProviderInterface,
               DependencyIndicatorInterface,
               FilterProviderInterface,
               FormElementProviderInterface,
               HydratorProviderInterface,
               InputFilterProviderInterface,
               LocatorRegisteredInterface,
               LogProcessorProviderInterface,
               LogWriterProviderInterface,
               RouteProviderInterface,
               SerializerProviderInterface,
               ServiceProviderInterface,
               ValidatorProviderInterface,
               ViewHelperProviderInterface
{
    use DirectoryProviderTrait, NamespaceProviderTrait;

    /**
     *
     *
     * @var array
     */
    protected $moduleConfig = [];

    /**
     *
     *
     * @var array
     */
    protected $modules = [];

    /**
     *
     *
     * @param ModuleManagerInterface $moduleManager
     *
     * @return void
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        //TODO : Remember to keep the init() method as lightweight as possible
        $this->modules = $moduleManager->getModules();
        $events        = $moduleManager->getEventManager();
        $sharedManager = $events->getSharedManager();

        if ($this instanceof Feature\ModuleLoadPostListenerInterface) {
            /** @noinspection PhpUndefinedCallbackInspection */
            $events->attach(
                ModuleEvent::EVENT_LOAD_MODULES_POST,
                [
                    $this,
                    'onModuleLoadPost'
                ]
            );
        }

        if ($this instanceof Feature\ApplicationDispatchListenerInterface) {
            /** @noinspection PhpUndefinedCallbackInspection */
            $events->attach(
                MvcEvent::EVENT_DISPATCH,
                [
                    $this,
                    'onDispatch'
                ]
            );
        }

        if ($this instanceof Feature\ApplicationDispatchErrorListenerInterface) {
            /** @noinspection PhpUndefinedCallbackInspection */
            $events->attach(
                MvcEvent::EVENT_DISPATCH_ERROR,
                [
                    $this,
                    'onDispatchError'
                ]
            );
        }

        if (strpos(php_sapi_name(), 'cli') !== 0) {
            if ($this instanceof Feature\RoutePrepareListenerInterface) {
                /** @noinspection PhpUndefinedCallbackInspection */
                $sharedManager->attach(
                    'Zend\Mvc\Application',
                    MvcEvent::EVENT_ROUTE,
                    [
                        $this,
                        'onRoutePrepare'
                    ],
                    1000
                );
            }

            if ($this instanceof Feature\RoutePostListenerInterface) {
                /** @noinspection PhpUndefinedCallbackInspection */
                $sharedManager->attach(
                    'Zend\Mvc\Application',
                    MvcEvent::EVENT_ROUTE,
                    [
                        $this,
                        'onRoutePost'
                    ],
                    -1000
                );
            }
        }
    }

    /**
     *
     *
     * @param string $module
     *
     * @return bool
     */
    public function isModulePresent($module)
    {
        return array_search($module, $this->modules, true);
    }

    /**
     * Get autoloader config.
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        $autoloader = [];
        $directory  = $this->getDirectory();
        $classmap   = $directory . DIRECTORY_SEPARATOR . '../autoload_classmap.php';

        if (file_exists($classmap)) {
            $autoloader['Zend\Loader\ClassMapAutoloader'] = [$classmap];
        }

        $autoloader['Zend\Loader\StandardAutoloader'] = [
            'namespaces' => [
                $this->getNamespace() => $directory . DIRECTORY_SEPARATOR . 'src'
            ]
        ];

        return array_merge($autoloader, $this->getFileConfig('autoloader'));
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return $this->getModuleConfig('module');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getServiceConfig()
    {
        return $this->getModuleConfig('service-manager');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getControllerConfig()
    {
        return $this->getModuleConfig('controller');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getControllerPluginConfig()
    {
        return $this->getModuleConfig('controller-plugin');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getViewHelperConfig()
    {
        return $this->getModuleConfig('view-helper');
    }

    /**
     * Expected to return an array of modules on which the current one depends on.
     *
     * @return array|\Traversable
     */
    public function getModuleDependencies()
    {
        return $this->getModuleConfig('dependency');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getFilterConfig()
    {
        return $this->getModuleConfig('filter');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getFormElementConfig()
    {
        return $this->getModuleConfig('form-element');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getHydratorConfig()
    {
        return $this->getModuleConfig('hydrator');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getInputFilterConfig()
    {
        return $this->getModuleConfig('input-filter');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getLogProcessorConfig()
    {
        return $this->getModuleConfig('log-processor');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getLogWriterConfig()
    {
        return $this->getModuleConfig('log-writer');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getRouteConfig()
    {
        return $this->getModuleConfig('router');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getSerializerConfig()
    {
        return $this->getModuleConfig('serializer');
    }

    /**
     * Expected to return {@link \Zend\ServiceManager\Config} object or array to seed such an object.
     *
     * @return array|\Traversable
     */
    public function getValidatorConfig()
    {
        return $this->getModuleConfig('validator');
    }

    /**
     *
     *
     * @param string $configName
     *
     * @return array|\Traversable
     */
    protected function getModuleConfig($configName)
    {
        if (!array_key_exists($configName, $this->moduleConfig)) {
            $this->moduleConfig[$configName] = $this->getFileConfig($configName);
        }

        return $this->moduleConfig[$configName];
    }

    /**
     * Return and merge configuration for this module from the default location.
     *
     * @param string $file
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function getFileConfig($file)
    {
        $directory    = $this->getDirectory() . DIRECTORY_SEPARATOR . 'config';
        $globFileName = $file . '.{,*.}config.php';
        $filePatterns = [
            $directory . DIRECTORY_SEPARATOR . $globFileName,
            $directory . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $globFileName,
            $directory . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $globFileName
        ];

        $config = [];

        foreach ($filePatterns as $pattern) {
            $config = ArrayUtils::merge($config, $this->loadGlobPatternConfig($pattern));
        }

        return $config;
    }

    /**
     *
     *
     * @param string $pattern
     *
     * @return array
     */
    private function loadGlobPatternConfig($pattern)
    {
        /**
         * glob() returns false on error. On some systems,
         * glob() will return false (instead of an empty array) if no files are found.
         * Treat both in the same way - no config will be loaded.
         */
        $files  = glob($pattern, GLOB_BRACE) ?: [];
        $config = [];

        if (is_array($files)) {
            foreach ($files as $fileName) {
                /** @noinspection PhpIncludeInspection */
                $config = ArrayUtils::merge($config, include $fileName);
            }
        }

        return $config;
    }
}
