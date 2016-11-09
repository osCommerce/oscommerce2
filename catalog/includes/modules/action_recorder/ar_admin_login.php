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

  class ar_admin_login {
    var $code = 'ar_admin_login';
    var $title;
    var $description;
    var $sort_order = 0;
    var $minutes = 5;
    var $attempts = 3;
    var $identifier;

    function __construct() {
      $this->title = OSCOM::getDef('module_action_recorder_admin_login_title');
      $this->description = OSCOM::getDef('module_action_recorder_admin_login_description');

      if ($this->check()) {
        $this->minutes = (int)MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES;
        $this->attempts = (int)MODULE_ACTION_RECORDER_ADMIN_LOGIN_ATTEMPTS;
      }
    }

    function setIdentifier() {
      $this->identifier = HTTP::getIpAddress();
    }

    function canPerform($user_id, $user_name) {
      $OSCOM_Db = Registry::get('Db');

      $sql_query = 'select id from :table_action_recorder where module = :module';

      if (!empty($user_name)) {
        $sql_query .= ' and (user_name = :user_name or identifier = :identifier)';
      } else {
        $sql_query .= ' and identifier = :identifier';
      }

      $sql_query .= ' and date_added >= date_sub(now(), interval :limit_minutes minute) and success = 0 limit :limit_attempts';

      $Qcheck = $OSCOM_Db->prepare($sql_query);
      $Qcheck->bindValue(':module', $this->code);

      if (!empty($user_name)) {
        $Qcheck->bindValue(':user_name', $user_name);
      }

      $Qcheck->bindValue(':identifier', $this->identifier);
      $Qcheck->bindInt(':limit_minutes', $this->minutes);
      $Qcheck->bindInt(':limit_attempts', $this->attempts);
      $Qcheck->execute();

      if (count($Qcheck->fetchAll()) == $this->attempts) {
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
      return defined('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Allowed Minutes',
        'configuration_key' => 'MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES',
        'configuration_value' => '5',
        'configuration_description' => 'Number of minutes to allow login attempts to occur.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Allowed Attempts',
        'configuration_key' => 'MODULE_ACTION_RECORDER_ADMIN_LOGIN_ATTEMPTS',
        'configuration_value' => '3',
        'configuration_description' => 'Number of login attempts to allow within the specified period.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES', 'MODULE_ACTION_RECORDER_ADMIN_LOGIN_ATTEMPTS');
    }
  }
?>
