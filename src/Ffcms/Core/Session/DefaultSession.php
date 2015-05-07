<?php

namespace Ffcms\Core\Session;

use Ffcms\Core\Helper\Directory;
use Ffcms\Core\Interfaces\iSession;

class DefaultSession implements iSession
{
    protected $path;
    protected $lifetime = 1440;


    /**
     * Override global property's onLoad if exist
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        if (count($params) > 0) {
            foreach ($params as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->$property = $value;
                }
            }
        }
    }

    /**
     * PHP >= 5.4.0<br/>
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function close()
    {
        return true;
    }

    /**
     * PHP >= 5.4.0<br/>
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param int $session_id The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function destroy($session_id)
    {
        @unlink($this->path . '/session_' . $session_id);
    }

    /**
     * PHP >= 5.4.0<br/>
     * Cleanup old sessions
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function gc($maxlifetime)
    {
        if ($this->lifetime !== 1440) {
            $maxlifetime = $this->lifetime; // overwrite over abstract layer
        }
        foreach (glob($this->path . '/session_*') as $file) {
            if (!file_exists($file)) {
                continue;
            }
            if (filemtime($file) + $maxlifetime < time()) {
                @unlink($file);
            }
        }
        return true;
    }

    /**
     * PHP >= 5.4.0<br/>
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $session_id The session id.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function open($save_path, $session_id)
    {
        if (null === $this->path) {
            $this->path = $save_path;
        }
        if (!Directory::exist($this->path)) {
            Directory::create($this->path, 0777);
        }
        return true;
    }

    /**
     * PHP >= 5.4.0<br/>
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function read($session_id)
    {
        return (string)@file_get_contents($this->path . '/session_' . $session_id);
    }

    /**
     * PHP >= 5.4.0<br/>
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id The session id.
     * @param string $session_data <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function write($session_id, $session_data)
    {
        return @file_put_contents($this->path . '/session_' . $session_id, $session_data);
    }

    /**
     * Initialize session data
     */
    public function start()
    {
        if ($this->isOpen()) {
            return;
        }

        // register custom handler's
        $this->registerHandler();
        // set cookie lifetime
        @ini_set('session.cookie_lifetime', $this->lifetime);
        @ini_set('session.gc_maxlifetime', $this->lifetime);
        @session_set_cookie_params($this->lifetime);
        // open session
        @session_start();
    }

    /**
     * Hook session handler function
     */
    public function registerHandler()
    {
        @session_set_save_handler($this, true);
    }

    /**
     * Check is current session always open'd
     * @return bool
     */
    public function isOpen()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}