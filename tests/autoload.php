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

error_reporting(E_ALL | E_STRICT);

/** Changes the current umask */
umask(0);

/**
 * Define application environment.
 * User can set the value in .htacess file located at the root directory.
 * The common values are:
 * - development : Indicates that we are in development environment
 * - production  : Indicates the production environment
 */
defined('APPLICATION_ENV') or define('APPLICATION_ENV', 'development');

/**
 * Define the project name.
 */
define('XO_PROJECT_NAME', 'Xloit\Bridge\Zend\ModuleManager\Tests');

/**
 * Define minimum PHP version.
 */
define('XO_PHP_VERSION', '5.4.0');

/** Sets the default timezone */
if (getenv('APPLICATION_TIMEZONE')) {
    defined('XO_TIMEZONE') or define('XO_TIMEZONE', getenv('APPLICATION_TIMEZONE'));
} else {
    /** @noinspection PhpUsageOfSilenceOperatorInspection */
    defined('XO_TIMEZONE') or define('XO_TIMEZONE', @ini_get('date.timezone'));
}

/** @noinspection PhpUsageOfSilenceOperatorInspection */
@ini_set('date.timezone', XO_TIMEZONE);

/** Sets error mode */
/** @noinspection PhpUsageOfSilenceOperatorInspection */
defined('XO_PRINT_ERROR_BACKTRACE') or define(
'XO_PRINT_ERROR_BACKTRACE',
    (APPLICATION_ENV === 'development') || @ini_get('display_errors')
);
defined('XO_ENABLE_ERROR_HANDLER') or define('XO_ENABLE_ERROR_HANDLER', true);
/** @noinspection PhpUsageOfSilenceOperatorInspection */
@ini_set('display_errors', XO_PRINT_ERROR_BACKTRACE);

/** Default string function encoding. */
if (getenv('APPLICATION_ENCODING')) {
    defined('XO_ENCODING') or define('XO_ENCODING', getenv('APPLICATION_ENCODING'));
} else {
    defined('XO_ENCODING') or define('XO_ENCODING', 'UTF-8');
}
mb_internal_encoding(XO_ENCODING);

/** Sets the directory separator */
define('DS', DIRECTORY_SEPARATOR);

/** PHP version validation */
if (version_compare(phpversion(), '5.4.0', '<') === true) {
    throw new RuntimeException(
        sprintf(
            'ERROR: To run %s, please install PHP %s or greater. %s has detected your PHP version is %s.',
            XO_PROJECT_NAME, XO_PHP_VERSION, XO_PROJECT_NAME, phpversion()
        )
    );
}

require __DIR__ . DS . 'Bootstrap.php';

/** @noinspection PhpIncludeInspection */
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    /** @noinspection PhpIncludeInspection */
    $loader = include __DIR__ . '/../vendor/autoload.php';
}

Bootstrap::init();
