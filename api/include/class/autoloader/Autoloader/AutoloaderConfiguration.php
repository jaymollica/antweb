<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class AutoloaderConfiguration
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
 * @category  PHP
 * @package   Autoloader
 * @author    Markus Malkusch <markus@malkusch.de>
 * @copyright 2009 - 2010 Markus Malkusch
 * @license   http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version   SVN: $Id$
 * @link      http://php-autoloader.malkusch.de/en/
 */

/**
 * Required classes
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Configuration_File',
    dirname(__FILE__) . '/exception/AutoloaderException_Configuration_File.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Configuration_File_Exists',
    dirname(__FILE__)
        . '/exception/AutoloaderException_Configuration_File_Exists.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Configuration_MissingSection',
    dirname(__FILE__)
        . '/exception/AutoloaderException_Configuration_MissingSection.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Configuration_Setting_Exists',
    dirname(__FILE__)
        . '/exception/AutoloaderException_Configuration_Setting_Exists.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Configuration_Setting_Object',
    dirname(__FILE__)
        . '/exception/AutoloaderException_Configuration_Setting_Object.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Configuration_Setting_Object_Exists',
    dirname(__FILE__)
        . '/exception/AutoloaderException_Configuration_Setting_Object_Exists.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_IO',
    dirname(__FILE__)
        . '/index/exception/AutoloaderException_Index_IO.php'
);

/**
 * Configuration for the Autoloader
 *
 * @category PHP
 * @package  Autoloader
 * @author   Markus Malkusch <markus@malkusch.de>
 * @license  http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version  Release: 1.12
 * @link     http://php-autoloader.malkusch.de/en/
 */
class AutoloaderConfiguration
{

    const
    /**
     * Section in the configuration file
     */
    SECTION = "autoloader",
    /**
     * Index file
     */
    FILE = "file",
    /**
     * Memcache host
     */
    MEMCACHE_HOST = "memcache.host",
    /**
     * Memcache port
     */
    MEMCACHE_PORT = "memcache.port",
    /**
     * PDO DSN
     */
    PDO_DSN = "pdo.dsn",
    /**
     * PDO user name
     */
    PDO_USERNAME = "pdo.username",
    /**
     * PDO password
     */
    PDO_PASSWORD = "pdo.password",
    /**
     * File iterator implementation
     */
    FILE_ITERATOR = "file_iterator",
    /**
     * Index implementation
     */
    INDEX = "index";

    static private
    /**
     * @var AutoloaderConfiguration
     */
    $_instance;

    private
    /**
     * @var Array
     */
    $_configuration = array();

    /**
     * Creates the only instance of this class
     *
     * @see getInstance()
     * @return void
     */
    static public function classConstructor()
    {
        self::$_instance = new self(dirname(__FILE__) . "/../autoloader.ini");
    }

    /**
     * Returns the only instance of this class
     *
     * @see classConstructor()
     * @return AutoloaderConfiguration
     */
    static public function getInstance()
    {
        return self::$_instance;
    }

    /**
     * Private constructor as this is a singleton
     *
     * @param String $configurationFile configuration file
     *
     * @throws AutoloaderException_Configuration_File
     * @throws AutoloaderException_Configuration_File_Exists
     * @throws AutoloaderException_Configuration_MissingSection
     */
    private function __construct($configurationFile)
    {
        $configuration = @parse_ini_file($configurationFile, true);

        // Error handling
        if (empty($configuration)) {
            if (! file_exists($configurationFile)) {
                throw new AutoloaderException_Configuration_File_Exists(
                    $configurationFile
                );

            }

            $error = error_get_last();
            throw new AutoloaderException_Configuration_File(
                "could not get configuration from '$configurationFile':"
                . $error['message']
            );

        }

        // the autoloader section must exist
        if (empty($configuration[self::SECTION])) {
            throw new AutoloaderException_Configuration_MissingSection(
                "missing configuration section " . self::SECTION
            );

        }

        $this->_configuration = $configuration[self::SECTION];
    }

    /**
     * Private __clone() as this is a singleton
     *
     * @return void
     */
    private function __clone()
    {

    }
    
    /**
     * Returns if a setting exists
     *
     * @param string $setting Setting
     * 
     * @return bool
     */
    public function hasSetting($setting)
    {
        return array_key_exists($setting, $this->_configuration);
    }

    /**
     * Read a configuration setting
     *
     * If no default value is set and the settings doesn't exist, a
     * AutoloaderException_Configuration_Setting_Exists is raised.
     *
     * @param String $setting setting name
     * @param Mixed  $default default value
     *
     * @return Mixed
     * @throws AutoloaderException_Configuration_Setting_Exists
     */
    public function getValue($setting, $default = null)
    {
        // Check if setting exists
        if (! $this->hasSetting($setting)) {
            if (is_null($default)) {
                throw new AutoloaderException_Configuration_Setting_Exists(
                    $setting
                );

            }
            return $default;

        }
        
        return $this->_configuration[$setting];
    }

    /**
     * Read a classname from configuration
     *
     * @param String $setting    setting name
     * @param Array  $parameters parameters for the constructor
     * @param Mixed  $default    default class
     *
     * @return Mixed instance of the classname
     * @throws AutoloaderException_Configuration_Setting_Exists
     * @throws AutoloaderException_Configuration_Setting_Object
     * @throws AutoloaderException_Configuration_Setting_Object_Exists
     */
    public function getObject(
        $setting,
        Array $parameters = array(),
        $default = null
    ) {
        try {
            $classname = $this->getValue($setting, $default);
            $class     = new ReflectionClass($classname);
            return
                empty($parameters)
                ? $class->newInstance()
                : $class->newInstanceArgs($parameters);

        } catch (ReflectionException $e) {
            if (! class_exists($classname, false)) {
                throw new AutoloaderException_Configuration_Setting_Object_Exists(
                    $setting, $classname
                );

            } else {
                throw new AutoloaderException_Configuration_Setting_Object(
                    $e->getMessage(), 0, $e
                );

            }
        }
    }

    /**
     * Returns a connected Memcache instance
     *
     * @return Memcache
     * @throws AutoloaderException_Configuration_Setting_Exists
     * @throws AutoloaderException_Index_IO
     */
    private function _getMemcache()
    {
        $host = $this->getValue(self::MEMCACHE_HOST, 'localhost');
        $port = $this->getValue(self::MEMCACHE_PORT, 11211);
        
        $memcache    = new Memcache();
        $isConnected = $memcache->connect($host, $port);

        if (! $isConnected) {
            throw new AutoloaderException_Index_IO(
                "Could not connect to memcached at $host:$port."
            );

        }
        return $memcache;
    }

    /**
     * Returns a configured PDO instance
     *
     * @return PDO
     * @throws AutoloaderException_Configuration_Setting_Exists
     * @throws PDOException
     */
    private function _getPDO()
    {
        $pdo
            = new PDO(
                $this->getValue(self::PDO_DSN),
                $this->getValue(self::PDO_USERNAME, ''),
                $this->getValue(self::PDO_PASSWORD, '')
            );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    /**
     * Returns an AutoloaderIndex implementation
     *
     * @return AutoloaderIndex
     * @throws AutoloaderException_Configuration_Setting_Exists
     * @throws PDOException
     * @throws AutoloaderException_Index_IO
     */
    public function getIndex()
    {
        $implementation = $this->getValue(self::INDEX);

        // declare the constructor paramters
        switch (strtolower($implementation)) {

            case 'autoloaderindex_pdo':
                $parameter = array($this->_getPDO());
                break;

            case 'autoloaderindex_memcache':
                $parameter = array($this->_getMemcache());
                break;

            default:
                $parameter = array();
                break;

        }

        $index
            = $this->getObject(
                self::INDEX,
                $parameter,
                'AutoloaderIndex_SerializedHashtable_GZ'
            );

        // set a configured file
        if (
            $index instanceof AutoloaderIndex_File
            && $this->hasSetting(self::FILE)
        ) {
            $index->setIndexPath(
                $this->getValue(self::FILE)
            );

        }

        return $index;
    }

}