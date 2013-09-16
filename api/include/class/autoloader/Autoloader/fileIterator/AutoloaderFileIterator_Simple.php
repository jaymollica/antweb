<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderFileIterator_Simple
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
 * @subpackage FileIterator
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id$
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The parent class must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator',
    dirname(__FILE__) . '/AutoloaderFileIterator.php'
);

/**
 * Searches all files without any logic
 *
 * It uses a stack of DirectoryIterator objects for searching recursively.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage FileIterator
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.12
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::searchPath()
 */
class AutoloaderFileIterator_Simple extends AutoloaderFileIterator
{

    private
    /**
     * @var Array The stack hold DirectoryIterator objects for recursion.
     */
    $_stack = array(),
    /**
     * @var DirectoryIterator The current used DirectoryIterator object.
     */
    $_iterator;

    /**
     * Returns the path of the current file
     *
     * @see Iterator::current()
     * @return String
     */
    public function current()
    {
        return $this->_iterator->current()->getPathname();
    }

    /**
     * Returns the key of the current DirectoryIterator object.
     *
     * This key is not meant to be used or to be distinct.
     *
     * @see Iterator::key()
     * @return String
     */
    public function key()
    {
        return $this->_iterator->key();
    }

    /**
     * Calls next() on the current DirectoryIterator
     *
     * @see Iterator::next()
     * @return void
     */
    public function next()
    {
        $this->_iterator->next();
    }

    /**
     * Clears the stack and sets the current iterator to a new instance of
     * DirectoryIterator with the class path of $autoloader.
     *
     * @see Iterator::rewind()
     * @return void
     */
    public function rewind()
    {
        $this->_stack    = array();
        $this->_iterator = new DirectoryIterator($this->autoloader->getPath());
        $this->_iterator->rewind();
    }

    /**
     * Does the recursion magic
     * 
     * If the current path is a directory, the current iterator is put on the stack
     * and a new DirectoryIterator with the current path becomes the current
     * iterator.
     * 
     * If the current iterator is no more valid the last iterator is pulled from the
     * stack and becomes the current iterator. If no more iterators are left on the
     * stack, this iterator is no more valid.
     *
     * @see Iterator::valid()
     * @return bool
     */
    public function valid()
    {
        while (true) {
            if (is_null($this->_iterator)) {
                return false;

            }

            // recurse backwards
            if (! $this->_iterator->valid()) {
                $this->_iterator = array_pop($this->_stack);
                continue;

            }

            $path = $this->_iterator->current()->getPathname();

            // apply file filters
            foreach ($this->skipPatterns as $pattern) {
                if (preg_match($pattern, $path)) {
                    $this->_iterator->next();
                    continue 2;

                }

            }

            // skip . and ..
            $isNavigationLink = in_array(
                $this->_iterator->current()->getFilename(), array('.', '..')
            );
            if ($isNavigationLink) {
                $this->_iterator->next();
                continue;

            }

            // recurse through the directories
            if ($this->_iterator->current()->isDir()) {
                $this->_iterator->next();
                $this->_stack[]  = $this->_iterator;
                $this->_iterator = new DirectoryIterator($path);
                $this->_iterator->rewind();
                continue;

            }

            // skip too big files
            $isTooBig
                = ! empty($this->skipFilesize)
                && $this->_iterator->current()->getSize() > $this->skipFilesize;
            if ($isTooBig) {
                $this->_iterator->next();
                continue;

            }

            return true;
        }
    }

}