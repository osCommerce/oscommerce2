<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

class HTML
{
    public static function output($string, $translate = null)
    {
        if (!isset($translate)) {
            $translate = [
                '"' => '&quot;'
            ];
        }

        return strtr(trim($string), $translate);
    }

    public static function outputProtected($string)
    {
        return htmlspecialchars(trim($string));
    }

    public static function sanitize($string)
    {
        $patterns = [
            '/ +/',
            '/[<>]/'
        ];

        $replace = [
            ' ',
            '_'
        ];

        return preg_replace($patterns, $replace, trim($string));
    }

    public static function getBatchTotalPages($text, $total, $pageset_number = 1)
    {
        $pageset_number = is_numeric($pageset_number) && ($pageset_number > 0) ? $pageset_number : 1;

        if ($total < 1) {
            $from = 0;
        } else {
            $from = max(($pageset_number * MAX_DISPLAY_PAGE_LINKS) - MAX_DISPLAY_PAGE_LINKS, 1);
        }

        $to = min($pageset_number * MAX_DISPLAY_PAGE_LINKS, $total);

        return sprintf($text, $from, $to, $total);
    }

    public static function getPageLinks($total, $parameters = null, $keyword = 'page')
    {
        global $PHP_SELF, $request_type;

        $page_number = (isset($_GET[$keyword]) && is_numeric($_GET[$keyword]) && ($_GET[$keyword] > 0)) ? $_GET[$keyword] : 1;
        $number_of_pages = ceil($total / MAX_DISPLAY_PAGE_LINKS);

        if (!empty($parameters) && (substr($parameters, -1) != '&')) {
            $parameters .= '&';
        }

        $output = '<ul class="pagination">';

// previous button - not displayed on first page
        if ($page_number > 1) {
            $output .= '<li><a href="' . tep_href_link($PHP_SELF, $parameters . $keyword . '=' . ($page_number - 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' ">&laquo;</a></li>';
        } else {
            $output .= '<li class="disabled"><span>&laquo;</span></li>';
        }

// check if number_of_pages > $max_page_links
        $cur_window_num = (int)($page_number / $total);
        if ($page_number % $total) {
            $cur_window_num++;
        }

        $max_window_num = (int)($number_of_pages / $total);
        if ($number_of_pages % $total) {
            $max_window_num++;
        }

// previous window of pages
        if ($cur_window_num > 1) {
            $output .= '<li><a href="' . tep_href_link($PHP_SELF, $parameters . $keyword . '=' . (($cur_window_num - 1) * $total), $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $total) . ' ">...</a></li>';
        }

// page nn button
        for ($jump_to_page = 1 + (($cur_window_num - 1) * $total); ($jump_to_page <= ($cur_window_num * $total)) && ($jump_to_page <= $number_of_pages); $jump_to_page++) {
            if ($jump_to_page == $page_number) {
                $output .= '<li class="active"><a href="' . tep_href_link($PHP_SELF, $parameters . $keyword . '=' . $jump_to_page, $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '<span class="sr-only">(current)</span></a></li>';
            } else {
                $output .= '<li><a href="' . tep_href_link($PHP_SELF, $parameters . $keyword . '=' . $jump_to_page, $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '</a></li>';
            }
        }

// next window of pages
        if ($cur_window_num < $max_window_num) {
            $output .= '<a href="' . tep_href_link($PHP_SELF, $parameters . $keyword . '=' . (($cur_window_num) * $total + 1), $request_type) . '" class="pageResults" title=" ' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $total) . ' ">...</a>&nbsp;';
        }

// next button
        if (($page_number < $number_of_pages) && ($number_of_pages != 1)) {
            $output .= '<li><a href="' . tep_href_link($PHP_SELF, $parameters . $keyword . '=' . ($page_number + 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' ">&raquo;</a></li>';
        } else {
            $output .= '<li class="disabled"><span>&raquo;</span></li>';
        }

        $output .= '</ul>';

        return $output;
    }
}
