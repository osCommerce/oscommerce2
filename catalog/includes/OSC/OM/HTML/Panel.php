<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM\HTML;

class Panel
{
    public static function get($heading, $body, $params = null)
    {
        if (!isset($params)) {
            $params = [];
        }

        if (!isset($params['type']) || !in_array($params['type'], ['default', 'primary', 'success', 'info', 'warning', 'danger'])) {
            $params['type'] = 'default';
        }

        $result = '<div class="panel panel-' . $params['type'] . ' oscom-panel"';

        if (isset($params['params'])) {
            $result .= ' ' . $params['params'];
        }

        $result .= '>';

        if (isset($heading) && !empty($heading)) {
            $result .= static::buildHeading($heading);
        }

        if (isset($body) && !empty($body)) {
            $result .= static::buildBody($body);
        }

        $result .= '</div>';

        return $result;
    }

    protected static function buildHeading($data)
    {
        $result = '<div class="panel-heading">';

        foreach ($data as $d) {
            $result .= '<h3 class="panel-title">' . $d['text'] . '</h3>';
        }

        $result .= '</div>';

        return $result;
    }

    protected static function buildBody($data)
    {
        $result = '<div class="panel-body"><div class="container-fluid">';

        $form_set = false;

        if (isset($data['form'])) {
            $result .= $data['form'];

            $form_set = true;

            unset($data['form']);
        }

        foreach ($data as $l) {
            $result .= '<div class="row">';

            if (isset($l['align'])) {
                $result .= '<div class="text-' . $l['align'] . '">';
            }

            $result .= $l['text'];

            if (isset($l['align'])) {
                $result .= '</div>';
            }

            $result .= '</div>';
        }

        if ($form_set === true) {
            $result .= '</form>';
        }

        $result .= '</div></div>';

        return $result;
    }
}
