<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderException_Include_ClassConstructor
 *
 * PHP version 5
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.
 * If not, see <http://php-autoloader.malkusch.de/en/license/>.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Exception
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id$
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The parent class must be loaded. As this exception might be raised in the
 * InternalAutoloader, it is loaded by require_once.
 */
require_once dirname(__FILE__) . '/AutoloaderException_Include.php';

/**
 * Raised if the class constructor failed
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Exception
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.12
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AbstractAutoloader::_callClassConstructor()
 */
class AutoloaderException_Include_ClassConstructor extends
    AutoloaderException_Include
{

    /**
     * Sets the classname and the cause
     *
     * @param String    $class classname
     * @param Exception $cause cause for this exception
     */
    public function __construct($class, Exception $cause)
    {
        $causeExceptionName = get_class($cause);
        parent::__construct(
            "$causeExceptionName raised while calling class constructor"
            . " for '$class': {$cause->getMessage()})",
            0,
            $cause
        );
    }

}