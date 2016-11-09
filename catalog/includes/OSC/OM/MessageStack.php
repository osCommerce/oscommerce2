<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class MessageStack
{
    protected $data = [];

    public function __construct()
    {
        register_shutdown_function(function() {
            if (!empty($this->data)) {
                $_SESSION['MessageStack_Data'] = $this->data;
            }
        });

        Registry::get('Hooks')->watch('Session', 'StartAfter', 'execute', function() {
            if (isset($_SESSION['MessageStack_Data']) && !empty($_SESSION['MessageStack_Data'])) {
                foreach ($_SESSION['MessageStack_Data'] as $group => $messages) {
                    foreach ($messages as $message) {
                        $this->add($message['text'], $message['type'], $group);
                    }
                }

                unset($_SESSION['MessageStack_Data']);
            }
        });

        Registry::get('Hooks')->watch('Account', 'LogoutAfter', 'execute', function() {
          $this->reset('main');
        });
    }

    public function add($message, $type = 'error', $group = 'main')
    {
        switch ($type) {
            case 'error':
                $type = 'danger';
                break;
        }

        $stack = [
            'text' => $message,
            'type' => $type
        ];

        if (!$this->exists($group) || !in_array($stack, $this->data[$group])) {
            $this->data[$group][] = $stack;
        }
    }

    public function reset($group = null)
    {
        if (isset($group)) {
            if ($this->exists($group)) {
                unset($this->data[$group]);
            }
        } else {
            $this->data = [];
        }
    }

    public function exists($group = null)
    {
        if (isset($group)) {
            return array_key_exists($group, $this->data);
        }

        return !empty($this->data);
    }

    public function get($group)
    {
        $result = '';

        if ($this->exists($group)) {
            $data = [];

            foreach ($this->data[$group] as $message) {
                $data['alert-' . $message['type']][] = $message['text'];
            }

            foreach ($data as $type => $messages) {
                $result .= '<div class="alert ' . HTML::outputProtected($type) . '" role="alert">';

                foreach ($messages as $message) {
                    $result .= '<p>' . $message . '</p>';
                }

                $result .= '</div>';
            }

            unset($this->data[$group]);
        }

        return $result;
    }

    public function getAll($group = null)
    {
        if (isset($group)) {
            if ($this->exists($group)) {
                return $this->data[$group];
            }

            return [];
        }

        return $this->data;
    }

    public function size($group = null)
    {
        if (isset($group)) {
            if ($this->exists($group)) {
                return count($this->data[$group]);
            }

            return 0;
        }

        return count($this->data);
    }
}
