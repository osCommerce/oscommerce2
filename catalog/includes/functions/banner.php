<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

////
// Sets the status of a banner
  function tep_set_banner_status($banners_id, $status) {
    $OSCOM_Db = Registry::get('Db');

    if ($status == '1') {
      return $OSCOM_Db->save('banners', ['status' => 1, 'date_status_change' => 'now()', 'date_scheduled' => 'null'], ['banners_id' => (int)$banners_id]);
    } elseif ($status == '0') {
      return $OSCOM_Db->save('banners', ['status' => 0, 'date_status_change' => 'now()'], ['banners_id' => (int)$banners_id]);
    } else {
      return -1;
    }
  }

////
// Auto activate banners
  function tep_activate_banners() {
    $OSCOM_Db = Registry::get('Db');

    $Qbanners = $OSCOM_Db->query('select banners_id from :table_banners where date_scheduled is not null and date_scheduled <= now() and status != 1');

    if ($Qbanners->fetch() !== false) {
      do {
        tep_set_banner_status($Qbanners->valueInt('banners_id'), 1);
      } while ($Qbanners->fetch());
    }
  }

////
// Auto expire banners
  function tep_expire_banners() {
    $OSCOM_Db = Registry::get('Db');

    $Qbanners = $OSCOM_Db->query('select b.banners_id, sum(bh.banners_shown) as banners_shown from :table_banners b, :table_banners_history bh where b.status = 1 and b.banners_id = bh.banners_id and ((b.expires_date is not null and now() >= b.expires_date) or (b.expires_impressions >= banners_shown)) group by b.banners_id');

    if ($Qbanners->fetch() !== false) {
      do {
        tep_set_banner_status($Qbanners->valueInt('banners_id'), 0);
      } while ($Qbanners->fetch());
    }
  }

////
// Display a banner from the specified group or banner id ($identifier)
  function tep_display_banner($action, $identifier) {
    $OSCOM_Db = Registry::get('Db');

    $banner = null;

    if ($action == 'dynamic') {
      $Qcheck = $OSCOM_Db->prepare('select banners_id from :table_banners where banners_group = :banners_group and status = 1 limit 1');
      $Qcheck->bindValue(':banners_group', $identifier);
      $Qcheck->execute();

      if ($Qcheck->fetch() !== false) {
        $Qbanner = $OSCOM_Db->prepare('select banners_id, banners_title, banners_image, banners_html_text from :table_banners where banners_group = :banners_group and status = 1 order by rand() limit 1');
        $Qbanner->bindValue(':banners_group', $identifier);
        $Qbanner->execute();

        $banner = $Qbanner->fetch();
      }
    } elseif ($action == 'static') {
      if (is_array($identifier)) {
        $banner = $identifier;
      } else {
        $Qbanner = $OSCOM_Db->prepare('select banners_id, banners_title, banners_image, banners_html_text from :table_banners where banners_id = :banners_id and status = 1');
        $Qbanner->bindInt(':banners_id', $identifier);
        $Qbanner->execute();

        if ($Qbanner->fetch() !== false) {
          $banner = $Qbanner->toArray();
        }
      }
    }

    $output = '';

    if (isset($banner)) {
      if (!empty($banner['banners_html_text'])) {
        $output = $banner['banners_html_text'];
      } else {
        $output = '<a href="' . OSCOM::link('redirect.php', 'action=banner&goto=' . $banner['banners_id']) . '" target="_blank">' . HTML::image(OSCOM::linkImage($banner['banners_image']), $banner['banners_title']) . '</a>';
      }

      tep_update_banner_display_count($banner['banners_id']);
    }

    return $output;
  }

////
// Check to see if a banner exists
  function tep_banner_exists($action, $identifier) {
    $OSCOM_Db = Registry::get('Db');

    $result = false;

    if ($action == 'dynamic') {
      $Qcheck = $OSCOM_Db->prepare('select banners_id from :table_banners where banners_group = :banners_group and status = 1 limit 1');
      $Qcheck->bindValue(':banners_group', $identifier);
      $Qcheck->execute();

      $result = $Qcheck->fetch() !== false;
    } elseif ($action == 'static') {
      $Qcheck = $OSCOM_Db->prepare('select banners_id from :table_banners where banners_id = :banners_id and status = 1');
      $Qcheck->bindInt(':banners_id', $identifier);
      $Qcheck->execute();

      $result = $Qcheck->fetch() !== false;
    }

    return $result;
  }

////
// Update the banner display statistics
  function tep_update_banner_display_count($banner_id) {
    $OSCOM_Db = Registry::get('Db');

    $Qcheck = $OSCOM_Db->prepare('select banners_history_id from :table_banners_history where banners_id = :banners_id and date_format(banners_history_date, "%Y%m%d") = date_format(now(), "%Y%m%d") limit 1');
    $Qcheck->bindInt(':banners_id', $banner_id);
    $Qcheck->execute();

    if ($Qcheck->fetch() !== false) {
      $Qview = $OSCOM_Db->prepare('update :table_banners_history set banners_shown = banners_shown + 1 where banners_id = :banners_id and date_format(banners_history_date, "%Y%m%d") = date_format(now(), "%Y%m%d")');
      $Qview->bindInt(':banners_id', $banner_id);
      $Qview->execute();
    } else {
      $Qview = $OSCOM_Db->prepare('insert into :table_banners_history (banners_id, banners_shown, banners_history_date) values (:banners_id, 1, now())');
      $Qview->bindInt(':banners_id', $banner_id);
      $Qview->execute();
    }

    return $Qview->rowCount();
  }

////
// Update the banner click statistics
  function tep_update_banner_click_count($banner_id) {
    $OSCOM_Db = Registry::get('Db');

    $Qupdate = $OSCOM_Db->prepare('update :table_banners_history set banners_clicked = banners_clicked + 1 where banners_id = :banners_id and date_format(banners_history_date, "%Y%m%d") = date_format(now(), "%Y%m%d")');
    $Qupdate->bindInt(':banners_id', $banner_id);
    $Qupdate->execute();

    return $Qupdate->rowCount();
  }
?>
