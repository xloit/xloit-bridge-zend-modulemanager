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

/**
 * A {@link NamespaceProviderTrait} trait.
 *
 * @package Xloit\Bridge\Zend\ModuleManager\Feature
 */
trait NamespaceProviderTrait
{
    /**
     * Module namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Because __NAMESPACE__ in a trait returns the namespace for the trait, this workaround is required to get the
     * namespace of the class which uses the trait.
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getNamespace()
    {
        if (null === $this->namespace) {
            $this->namespace = (new ReflectionClass(static::class))->getNamespaceName();
        }

        return $this->namespace;
    }
}
