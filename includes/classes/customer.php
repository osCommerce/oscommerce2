<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class customer {
    protected $_is_logged_on = false;
    protected $_data = array();

    public function __construct() {
      if ( !isset($_SESSION['customer']) ) {
        $_SESSION['customer'] = $this->_data;
      }

      $this->_data =& $_SESSION['customer'];

      if ( isset($this->_data['id']) && is_numeric($this->_data['id']) && ($this->_data['id'] > 0) ) {
        $this->setIsLoggedOn(true);
      }
    }

    public function setIsLoggedOn($state) {
      if ( !is_bool($state) ) {
        $state = false;
      }

      $this->_is_logged_on = $state;
    }

    public function isLoggedOn() {
      return $this->_is_logged_on;
    }

    public function get($key = null) {
      if ( isset($key) ) {
        return $this->_data[$key];
      }

      return $this->_data;
    }

    public function getID() {
      return $this->get('id');
    }

    public function getFirstName() {
      return $this->get('first_name');
    }

    public function getLastName() {
      return $this->get('last_name');
    }

    public function getName() {
      $name = '';

      if ( isset($this->_data['first_name']) ) {
        $name .= $this->_data['first_name'];
      }

      if ( isset($this->_data['last_name']) ) {
        if ( !empty($name) ) {
          $name .= ' ';
        }

        $name .= $this->_data['last_name'];
      }

      return $name;
    }

    public function getGender() {
      return $this->get('gender');
    }

    public function hasEmailAddress() {
      return isset($this->_data['email_address']);
    }

    public function getEmailAddress() {
      return $this->_data['email_address'];
    }

    public function getCountryID() {
      return $this->_data['country_id'];
    }

    public function getZoneID() {
      return $this->_data['zone_id'];
    }

    public function getDefaultAddressID() {
      return $this->_data['default_address_id'];
    }

    public function setData($id) {
      global $OSCOM_PDO;

      $this->_data = array();

      if ( is_numeric($id) && ($id > 0) ) {
        $Qcustomer = $OSCOM_PDO->prepare('select customers_gender, customers_firstname, customers_lastname, customers_email_address, customers_default_address_id from :table_customers where customers_id = :customers_id');
        $Qcustomer->bindInt(':customers_id', $id);
        $Qcustomer->execute();

        if ( $Qcustomer->fetch() !== false ) {
          $this->setIsLoggedOn(true);
          $this->setID($id);
          $this->setGender($Qcustomer->value('customers_gender'));
          $this->setFirstName($Qcustomer->value('customers_firstname'));
          $this->setLastName($Qcustomer->value('customers_lastname'));
          $this->setEmailAddress($Qcustomer->value('customers_email_address'));

          if ( $Qcustomer->valueInt('customers_default_address_id') > 0 ) {
            $Qab = $OSCOM_PDO->prepare('select entry_country_id, entry_zone_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
            $Qab->bindInt(':address_book_id', $Qcustomer->valueInt('customers_default_address_id'));
            $Qab->bindInt(':customers_id', $id);
            $Qab->execute();

            if ( $Qab->fetch() !== false ) {
              $this->setCountryID($Qab->valueInt('entry_country_id'));
              $this->setZoneID($Qab->valueInt('entry_zone_id'));
              $this->setDefaultAddressID($Qcustomer->valueInt('customers_default_address_id'));
            }
          }
        }
      }

      return !empty($this->_data);
    }

    public function setID($id) {
      if ( is_numeric($id) && ($id > 0) ) {
        $this->_data['id'] = $id;
      }
    }

    public function setDefaultAddressID($id) {
      global $OSCOM_PDO;

      if ( is_numeric($id) && ($id > 0) ) {
        if ( !isset($this->_data['default_address_id']) || ($this->_data['default_address_id'] != $id) ) {
          $Qupdate = $OSCOM_PDO->prepare('update :table_customers set customers_default_address_id = :customers_default_address_id where customers_id = :customers_id');
          $Qupdate->bindInt(':customers_default_address_id', $id);
          $Qupdate->bindInt(':customers_id', $this->getID());
          $Qupdate->execute();
        }

        $this->_data['default_address_id'] = $id;
      }
    }

    public function hasDefaultAddress() {
      return isset($this->_data['default_address_id']) && is_numeric($this->_data['default_address_id']);
    }

    public function setGender($gender) {
      if ( (strtolower($gender) == 'm') || (strtolower($gender) == 'f') ) {
        $this->_data['gender'] = strtolower($gender);
      }
    }

    public function setFirstName($first_name) {
      $this->_data['first_name'] = $first_name;
    }

    public function setLastName($last_name) {
      $this->_data['last_name'] = $last_name;
    }

    public function setEmailAddress($email_address) {
      $this->_data['email_address'] = $email_address;
    }

    public function setCountryID($id) {
      $this->_data['country_id'] = $id;
    }

    public function setZoneID($id) {
      $this->_data['zone_id'] = $id;
    }

    public function reset() {
      $this->_is_logged_on = false;
      $this->_data = array();
    }
  }
?>
