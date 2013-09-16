<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Implements the class AutoloaderIndex_Memcache
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
 * @subpackage Index
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id$
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * Require needed classes
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex_SharedKeyValueStorage',
    dirname(__FILE__) . '/AutoloaderIndex_SharedKeyValueStorage.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_IO',
    dirname(__FILE__) . '/exception/AutoloaderException_Index_IO.php'
);

/**
 * Implements AutoloaderIndex with Memcache
 *
 * This index uses memcached for storing its index.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Index
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.12
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::setIndex()
 * @see        Autoloader::getIndex()
 * @see        Memcache
 * @see        http://www.memcached.org/
 */
class AutoloaderIndex_Memcache extends AutoloaderIndex_SharedKeyValueStorage
{

    private
    /**
     * @var Memcache
     */
    $_memcache;

    /**
     * Sets the memcache connection
     *
     * If no memcache object is given, a default connection to
     * localhost will be used.
     *
     * @param Memcache $memcache connected Memcache object
     *
     * @throws AutoloaderException_Index_IO if connection to localhost fails
     */
    public function __construct(Memcache $memcache = null)
    {
        // establish a default connection to localhost
        if (is_null($memcache)) {
            $memcache = new Memcache();
            if (! $memcache->connect('localhost')) {
                throw new AutoloaderException_Index_IO(
                    "Could not connect to memcached at localhost."
                );

            }
        }
        $this->_memcache = $memcache;
    }

    /**
     * Fetches the value for a key
     *
     * @param string $key key
     *
     * @return mixed
     * @throws AutoloaderException_Index_IO
     */
    protected function getValue($key)
    {
        $value = $this->_memcache->get($key);
        if ($value === false) {
            throw new AutoloaderException_Index_IO(
                "Could not fetch a value for key '$key'"
            );

        }
        return $value;
    }

    /**
     * Returns true if a key is in the storage
     *
     * @param string $key key
     *
     * @return bool
     */
    protected function hasValue($key)
    {
        $value = $this->_memcache->get($key);
        return $value !== false;
    }

    /**
     * Deletes a key value pair
     *
     * @param string $key key
     *
     * @return void
     * @throws AutoloaderException_Index_IO
     */
    protected function deleteValue($key)
    {
        if (! $this->_memcache->delete($key)) {
            throw new AutoloaderException_Index_IO(
                "Could not delete key '$key'"
            );

        }
    }

    /**
     * Sets the value for a key
     *
     * @param string $key     key
     * @param string $value   value
     *
     * @return void
     * @throws AutoloaderException_Index_IO
     */
    protected function setValue($key, $value) {
        if (! $this->_memcache->set($key, $value, 0, 0)) {
            throw new AutoloaderException_Index_IO(
                "Could not store key '$key'"
            );

        }
    }

}