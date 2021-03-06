<?php
namespace Suricate\Cache;

use Suricate;

/**
 * Memcache extension for Suricate
 *
 * @package Suricate
 * @author  Mathieu LESNIAK <mathieu@lesniak.fr>
 *
 * @property string $path           Storage path (default: app/storage/app)
 * @property int    $defaultExpiry  Key default expiry in sec
 */

class File extends Suricate\Cache
{
    protected $parametersList = array(
                                    'path',
                                    'defaultExpiry',
                                );
    private $handler;

    public function __construct()
    {
        $this->handler          = false;
        $this->path             = app_path() . '/storage/app/';
        $this->defaultExpiry    = 3600;
    }

    public function getDefaultExpiry()
    {
        return $this->defaultExpiry;
    }

    public function setDefaultExpiry($expiry)
    {
        $this->defaultExpiry = $expiry;

        return $this;
    }
    
    /**
     * Put a value into memcache
     * @param string $variable Variable name
     * @param mixed $value    Value
     * @param int $expiry   Cache expiry
     */
    public function set($variable, $value, $expiry = null)
    {
        if ($expiry === null) {
            $expiry = $this->defaultExpiry;
        }
        $fp = fopen($this->path . $variable, 'w');
        fputs($fp, $value);
        fclose($fp);
        if ($expiry !== null) {
            $fp = fopen($this->path . $variable .'.expiry', 'w');
            fputs($fp, time() + $expiry);
            fclose($fp);
        }
    }

    public function get($variable)
    {
        if (is_readable($this->path . $variable)) {
            if (is_readable($this->path . $variable . '.expiry')) {
                $expiry = file_get_contents($this->path . $variable . '.expiry');
                $hasExpired = (time() - $expiry) > 0 ? 1 : -1;
            } else {
                $hasExpired = 0;
            }
            
            if ($hasExpired < 0) {
                return file_get_contents($this->path . $variable);
            } elseif ($hasExpired > 0) {
                unlink($this->path . $variable . '.expiry');
            }
        }
        return null;
    }

    public function delete($variable)
    {
        if (is_file($this->path . $variable)) {
            unlink($this->path . $variable);
        }
        if (is_file($this->path . $variable . '.expiry')) {
            unlink($this->path . $variable . '.expiry');
        }
    }
}
