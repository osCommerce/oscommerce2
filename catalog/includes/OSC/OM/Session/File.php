<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM\Session;

class File extends \OSC\OM\SessionAbstract implements \SessionHandlerInterface
{
    protected $path;

    public function __construct()
    {
        $this->setSavePath(OSCOM::BASE_DIR . 'Work/Session');

        session_set_save_handler($this, true);
    }

    public function exists($session_id)
    {
        $id = basename($session_id);

        return is_file($this->path . '/sess_' . $id);
    }

    public function open($save_path, $name)
    {
        if (!is_dir($save_path)) {
            mkdir($save_path, 0777);
        }

        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($session_id)
    {
        $id = basename($session_id);

        $result = false;

        if ($this->exists($id)) {
            $result = file_get_contents($this->path . '/sess_' . $id);
        }

        if ($result === false) {
            $result = '';
        }

        return $result;
    }

    public function write($session_id, $session_data)
    {
        $id = basename($session_id);

        return file_put_contents($this->path . '/sess_' . $id, $session_data) === false ? false : true;
    }

    public function destroy($session_id)
    {
        $id = basename($session_id);

        if ($this->exists($id)) {
            return unlink($this->path . '/sess_' . $id);
        }

        return true;
    }

    public function gc($maxlifetime)
    {
        foreach (glob($this->path . '/sess_*') as $file) {
            if (filemtime($file) + $maxlifetime < time()) {
                unlink($file);
            }
        }

        return true;
    }

    public function setSavePath($path)
    {
        if ((strlen($path) > 1) && (substr($path, -1) == '/')) {
            $path = substr($path, 0, -1);
        }

        session_save_path($path);

        $this->path = session_save_path();
    }
}
