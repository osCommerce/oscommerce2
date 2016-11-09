<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTTP;
  use OSC\OM\OSCOM;	
  use OSC\OM\Registry;

  class ar_contact_us {
    var $code = 'ar_contact_us';
    var $title;
    var $description;
    var $sort_order = 0;
    var $minutes = 15;
    var $identifier;

    function __construct() {
      $this->title = OSCOM::getDef('module_action_recorder_contact_us_title');
      $this->description = OSCOM::getDef('module_action_recorder_contact_us_description');

      if ($this->check()) {
        $this->minutes = (int)MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES;
      }
    }

    function setIdentifier() {
      $this->identifier = HTTP::getIpAddress();
    }

    function canPerform($user_id, $user_name) {
      $OSCOM_Db = Registry::get('Db');

      $sql_query = 'select id from :table_action_recorder where module = :module';

      if (!empty($user_id)) {
        $sql_query .= ' and (user_id = :user_id or identifier = :identifier)';
      } else {
        $sql_query .= ' and identifier = :identifier';
      }

      $sql_query .= ' and date_added >= date_sub(now(), interval :limit_minutes minute) and success = 1 limit 1';

      $Qcheck = $OSCOM_Db->prepare($sql_query);
      $Qcheck->bindValue(':module', $this->code);

      if (!empty($user_id)) {
        $Qcheck->bindInt(':user_id', $user_id);
      }

      $Qcheck->bindValue(':identifier', $this->identifier);
      $Qcheck->bindInt(':limit_minutes', $this->minutes);
      $Qcheck->execute();

      if ($Qcheck->fetch() !== false) {
        return false;
      }

      return true;
    }

    function expireEntries() {
      $Qdel = Registry::get('Db')->prepare('delete from :table_action_recorder where module = :module and date_added < date_sub(now(), interval :limit_minutes minute)');
      $Qdel->bindValue(':module', $this->code);
      $Qdel->bindInt(':limit_minutes', $this->minutes);
      $Qdel->execute();

      return $Qdel->rowCount();
    }

    function check() {
      return defined('MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Minimum Minutes Per E-Mail',
        'configuration_key' => 'MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES',
        'configuration_value' => '15',
        'configuration_description' => 'Minimum number of minutes to allow 1 e-mail to be sent (eg, 15 for 1 e-mail every 15 minutes)',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES');
    }
  }
?>
