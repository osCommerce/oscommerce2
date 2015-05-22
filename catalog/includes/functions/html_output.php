<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
// 2.4 DEPRECATED
  function tep_image_submit($image, $alt = '', $parameters = '') {
    $image_submit = '<input type="image" src="' . tep_output_string(DIR_WS_LANGUAGES . $_SESSION['language'] . '/images/buttons/' . $image) . '" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) $image_submit .= ' title=" ' . tep_output_string($alt) . ' "';

    if (tep_not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= ' />';

    return $image_submit;
  }

////
// Output a function button in the selected language
  function tep_image_button($image, $alt = '', $parameters = '') {
    return tep_image(DIR_WS_LANGUAGES . $_SESSION['language'] . '/images/buttons/' . $image, $alt, '', '', $parameters);
  }

////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return tep_image(DIR_WS_IMAGES . $image, '', $width, $height);
  }
