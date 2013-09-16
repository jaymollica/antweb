<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Implements the class AutoloaderIndex_SharedKeyValueStorage
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
    'AutoloaderIndex',
    dirname(__FILE__) . '/AutoloaderIndex.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index',
    dirname(__FILE__) . '/exception/AutoloaderException_Index.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_NotFound',
    dirname(__FILE__) . '/exception/AutoloaderException_Index_NotFound.php'
);

/**
 * Implements an index in a shared key value storage
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
 */
abstract class AutoloaderIndex_SharedKeyValueStorage extends AutoloaderIndex
{

    const
    /**
     * Stored classes are stored in an inverted Array with this key.
     */
    KEY_CLASSES = '__CLASSES__';

    /**
     * Fetches the value for a key
     *
     * @param string $key key
     *
     * @return mixed
     * @throws AutoloaderException_Index
     */
    abstract protected function getValue($key);

    /**
     * Returns true if a key is in the storage
     *
     * @param string $key key
     *
     * @return bool
     * @throws AutoloaderException_Index
     */
    abstract protected function hasValue($key);

    /**
     * Deletes a key value pair
     *
     * @param string $key key
     *
     * @return void
     * @throws AutoloaderException_Index
     */
    abstract protected function deleteValue($key);

    /**
     * Sets the value for a key
     *
     * @param string $key     key
     * @param string $value   value
     *
     * @return void
     * @throws AutoloaderException_Index
     */
    abstract protected function setValue($key, $value);

    /**
     * Returns the key with a prefix
     *
     * This prefixed key will be used to store and find keys in the shared
     * storage. This key transformation helps not to interfer with other
     * applications, which are using the shared storage.
     *
     * @param string $key unprefixed key
     *
     * @return string
     */
    private function _getPrefixedKey($key)
    {
        return __CLASS__ . "_" . $this->getContext() . "_" . $key;
    }

    /**
     * Returns all stored classes
     *
     * @return array
     */
    private function _getStoredClasses()
    {
        try {
            return $this->getValue($this->_getPrefixedKey(self::KEY_CLASSES));

        } catch (AutoloaderException_Index $e) {
            return array();

        }
    }

    /**
     * Adds a class to the stored classes array
     *
     * @param string $class a class name
     *
     * @return void
     * @throws AutoloaderException_Index
     */
    private function _addStoredClass($class)
    {
        $classes = $this->_getStoredClasses();
        $classes[$class] = $class;

        $this->setValue($this->_getPrefixedKey(self::KEY_CLASSES), $classes);
    }

    /**
     * Removes a class from the stored classes array
     *
     * @param string $class a class name
     *
     * @return void
     * @throws AutoloaderException_Index
     */
    private function _deleteStoredClass($class)
    {
        $classes = $this->_getStoredClasses();
        unset($classes[$class]);

        $this->setValue($this->_getPrefixedKey(self::KEY_CLASSES), $classes);
    }

    /**
     * Deletes all keys
     *
     * @return void
     */
    public function delete()
    {
        foreach ($this->_getStoredClasses() as $class) {
            $this->delete($this->_getPrefixedKey($class));
            
        }
        $this->delete($this->_getPrefixedKey(self::KEY_CLASSES));
    }

    /**
     * Returns the unfiltered path of a class definition
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index_NotFound
     * @throws AutoloaderException_Index
     * @return String
     */
    protected function getRawPath($class)
    {
        try {
            return $this->getValue($this->_getPrefixedKey($class));

        } catch (AutoloaderException_Index $e) {
            if (! $this->hasValue($this->_getPrefixedKey($class))) {
                throw new AutoloaderException_Index_NotFound();

            } else {
                throw $e;

            }
        }
    }

    /**
     * Returns all paths in the index
     *
     * @return Array
     * @throws AutoloaderException_Index_IO
     */
    public function getPaths()
    {
        $paths = array();
        foreach ($this->_getStoredClasses() as $class) {
            try {
                $paths[$class] = $this->getRawPath($class);

            } catch(AutoloaderException_Index_NotFound $e) {
                $this->_deleteStoredClass($class);

            }
        }
        return $paths;
    }

    /**
     * Returns the size of the index
     *
     * @see Countable
     * @return int
     */
    public function count()
    {
        return count($this->_getStoredClasses());
    }

    /**
     * Stores the path immediately persistent
     *
     * There is no sense in making the class paths persistent
     * during {@link save()}. It is stored immediately.
     *
     * @param String $class The class name
     * @param String $path  The filtered path
     *
     * @throws AutoloaderException_Index_IO
     * @return void
     */
    protected function setRawPath($class, $path)
    {
        $this->setValue($this->_getPrefixedKey($class), $path);

        // Store class name in the stored classes array
        $this->_addStoredClass($class);
    }

    /**
     * Deletes the path immediately persistent
     *
     * There is no sense in making the class paths persistent
     * during {@link save()}. It is deleted immediately.
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index_IO
     * @return void
     */
    protected function unsetRawPath($class)
    {
        $this->deleteValue($this->_getPrefixedKey($class));

        // Remove the class from the stored classes array
        $this->_deleteStoredClass($class);
    }

    /**
     * Returns true if the class is contained in the index
     *
     * @param String $class The class name
     *
     * @throws AutoloaderException_Index_IO
     * @return bool
     */
    public function hasPath($class)
    {
        return $this->hasValue($this->_getPrefixedKey($class));
    }

    /**
     * Does nothing as {@link setRawPath()} and {@link unsetRawPath()}
     * store immediately
     *
     * @see setRawPath()
     * @see unsetRawPath()
     * @return void
     */
    protected function saveRaw()
    {

    }

}