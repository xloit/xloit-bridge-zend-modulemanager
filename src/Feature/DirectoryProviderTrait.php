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

namespace Xloit\Bridge\Zend\ModuleManager\Feature;

use ReflectionClass;
use Xloit\Bridge\Zend\ModuleManager\Exception;

/**
 * A {@link DirectoryProviderTrait} trait.
 *
 * @package Xloit\Bridge\Zend\ModuleManager\Feature
 */
trait DirectoryProviderTrait
{
    /**
     * Module directory path.
     *
     * @var string
     */
    protected $directory;

    /**
     * Because __DIR__ in a trait returns the directory for the trait, this workaround is required to get
     * the directory of the class which uses the trait.
     *
     * @return string
     */
    public function getDirectory()
    {
        if (null === $this->directory) {
            $this->directory = dirname(dirname((new ReflectionClass(static::class))->getFileName()));
        }

        return $this->directory;
    }
}
