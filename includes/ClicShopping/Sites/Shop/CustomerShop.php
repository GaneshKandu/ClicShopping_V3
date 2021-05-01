<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @licence MIT - Portion of osCommerce 2.4
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */


  namespace ClicShopping\Sites\Shop;

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\HTML;
  use ClicShopping\OM\CLICSHOPPING;

  class CustomerShop
  {
    protected bool $_is_logged_on = false;
    protected array $_data = [];
    protected array $_data1 = [];

    protected $db;

    public function __construct()
    {
      if (isset($_SESSION['customer'])) {
        $this->_data =& $_SESSION['customer'];
      }

      if (isset($_SESSION['customer_group_id'])) {
        $this->_data1 =& $_SESSION['customer_group_id'];
      }

      if (isset($this->_data['id']) && is_numeric($this->_data['id']) && ($this->_data['id'] > 0)) {
        $this->setIsLoggedOn(true);
      }

      $this->db = Registry::get('Db');
    }

    /**
     * @param bool $state
     */
    public function setIsLoggedOn(bool $state)
    {
      if ($state === true) {
        $this->_is_logged_on = true;
      } else {
        $this->_is_logged_on = false;
      }
    }

    /**
     * @return bool
     */
    public function isLoggedOn(): bool
    {
      if ($this->_is_logged_on === true) {
        return true;
      }

      return false;
    }

    /**
     * @param string|null $key
     * @return array|mixed
     */
       public function get(string $key = null)
    {
      if (isset($key)) {
        return $this->_data[$key];
      } else {
        return $this->_data;
      }
    }

    /**
     * @return int
     */
    public function getID(): int
    {
      if (isset($this->_data['id']) && is_numeric($this->_data['id'])) {
        return (int)$this->_data['id'];
      }

      return 0;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
      if (isset($this->_data['first_name'])) {
        return $this->_data['first_name'];
      }

      return false;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
      if (isset($this->_data['last_name'])) {
        return $this->_data['last_name'];
      }

      return false;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
      $name = '';

      if (isset($this->_data['first_name'])) {
        $name .= $this->_data['first_name'];
      }

      if (isset($this->_data['last_name'])) {
        if (!empty($name)) {
          $name .= ' ';
        }

        $name .= $this->_data['last_name'];
      }

      return $name;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
      if (isset($this->_data['gender'])) {
        return $this->_data['gender'];
      }

      return false;
    }

    /**
     * @return bool
     */
    public function hasEmailAddress(): bool
    {
      return isset($this->_data['email_address']);
    }

    /**
     * @return string|null
     */
    public function getEmailAddress(): ?string
    {
      if (isset($this->_data['email_address'])) {
        return $this->_data['email_address'];
      }

      return false;
    }

    /**
     * @param string|null $telephone
     */
    public function setTelephone(?string $telephone)
    {
      $this->_data['customers_telephone'] = $telephone;
    }

    /**
     * @return string|null
     */
    public function getTelephone(): ?string
    {
      if (isset($this->_data['customers_telephone'])) {
        return $this->_data['customers_telephone'];
      }

      return false;
    }

    /**
     * @return int|null
     */
    public function getCountryID(): ?int
    {
      static $country_id = null;

      if (\is_null($country_id)) {
        if (isset($this->_data['country_id'])) {
          $country_id = $this->_data['country_id'];
        }
      }

      return $country_id;
    }

    /**
     * @return int|null
     */
    public function getZoneID(): ?int
    {
      static $zone_id = null;

      if (\is_null($zone_id)) {
        if (isset($this->_data['zone_id'])) {
          $zone_id = $this->_data['zone_id'];
        }
      }

      return $zone_id;
    }

    /**
     * @return int|null
     */
    public function getDefaultAddressID(): ?int
    {
      static $id = null;

      if (\is_null($id)) {
        if (isset($this->_data['default_address_id'])) {
          $id = $this->_data['default_address_id'];
        }
      }

      return $id;
    }

    /**
    * B2B
     * @return int
     */
    public function getCustomersGroupID(): int
    {
      $customersgroupid = 0;

      if (isset($this->_data1['customers_group_id'])) {
        $customersgroupid = $this->_data1['customers_group_id'];
      }

      return $customersgroupid;
    }

    /**
     * @param $id , customer id
     * @return bool
     */
    public function setData(int $id): bool
    {
      $this->_data = [];

      if (is_numeric($id) && ($id > 0)) {
        $Qcustomer = $this->db->prepare('select customers_gender,
                                                 customers_firstname,
                                                 customers_lastname,
                                                 customers_email_address,
                                                 customers_telephone,
                                                 customers_default_address_id
                                          from :table_customers
                                          where customers_id = :customers_id
                                        ');
        $Qcustomer->bindInt(':customers_id', $id);
        $Qcustomer->execute();

// B2B
        $QcustomerGroup = $this->db->prepare('select customers_group_id
                                               from :table_customers
                                               where customers_id = :customers_id
                                              ');
        $QcustomerGroup->bindInt(':customers_id', $id);
        $Qcustomer->bindInt(':customers_id', $id);
        $QcustomerGroup->execute();

        if ($QcustomerGroup->fetch() !== false) {
          $this->setCustomersGroupID($QcustomerGroup->value('customers_group_id'));
          $_SESSION['customer_group_id'] = $this->_data1;
        }

        if ($Qcustomer->fetch() !== false) {
          $this->setIsLoggedOn(true);
          $this->setID($id);
          $this->setGender($Qcustomer->value('customers_gender'));
          $this->setFirstName($Qcustomer->value('customers_firstname'));
          $this->setLastName($Qcustomer->value('customers_lastname'));
          $this->setEmailAddress($Qcustomer->value('customers_email_address'));
          $this->setTelephone($Qcustomer->value('customers_telephone'));

          if ($Qcustomer->valueInt('customers_default_address_id') > 0) {
            $Qab = $this->db->prepare('select entry_country_id,
                                               entry_zone_id
                                        from :table_address_book
                                        where address_book_id = :address_book_id
                                        and customers_id = :customers_id
                                      ');
            $Qab->bindInt(':address_book_id', $Qcustomer->valueInt('customers_default_address_id'));
            $Qab->bindInt(':customers_id', $id);
            $Qab->execute();

            if ($Qab->fetch() !== false) {
              $this->setCountryID($Qab->valueInt('entry_country_id'));
              $this->setZoneID($Qab->valueInt('entry_zone_id'));
              $this->setDefaultAddressID($Qcustomer->valueInt('customers_default_address_id'));
            }
          }

          $_SESSION['customer'] = $this->_data;
        }
      }

      return !empty($this->_data);
    }

    /**
     * @param int $id
     */
    public function setID(int $id)
    {
      if (is_numeric($id) && ($id > 0)) {
        $this->_data['id'] = $id;
      } else {
        $this->_data['id'] = false;
      }
    }

    /**
     * @param int $id
     */
    public function setDefaultAddressID(int $id)
    {
      if (is_numeric($id) && ($id > 0)) {

        if (!isset($this->_data['default_address_id']) || ($this->_data['default_address_id'] != $id)) {

          $Qupdate = $this->db->prepare('update :table_customers
                                         set customers_default_address_id = :customers_default_address_id
                                         where customers_id = :customers_id'
                                       );
          $Qupdate->bindInt(':customers_default_address_id', $id);
          $Qupdate->bindInt(':customers_id', $this->getID());
          $Qupdate->execute();
        }

        $this->_data['default_address_id'] = $id;
      } else {
        $this->_data['default_address_id'] = false;
      }
    }

    /**
     * @return bool
     */
    public function hasDefaultAddress() :bool
    {

      if (isset($this->_data['default_address_id']) && is_numeric($this->_data['default_address_id'])) {
        return true;
      }

      return false;
    }

    /**
     * @param string|null $gender
     */
    public function setGender(?string $gender)
    {
      if ((strtolower($gender) == 'm') || (strtolower($gender) == 'f')) {
        $this->_data['gender'] = strtolower($gender);
      } else {
        $this->_data['gender'] = false;
      }
    }

    /**
     * @param string|null $first_name
     */
    public function setFirstName(?string $first_name)
    {
      $this->_data['first_name'] = $first_name;
    }

    /**
     * @param string|null $last_name
     */
    public function setLastName(?string $last_name)
    {
      $this->_data['last_name'] = $last_name;
    }

    /**
     * @param string $email_address
     */
    public function setEmailAddress(string $email_address)
    {
      $this->_data['email_address'] = $email_address;
    }

    /**
     * @param int $id
     * @return int|null
     */
    public function getCustomerGuestAccount(int $id): ?int
    {
      $Qcustomer = $this->db->prepare('select customer_guest_account
                                        from :table_customers
                                        where customers_id = :customers_id
                                      ');

      $Qcustomer->bindInt(':customers_id', $id);
      $Qcustomer->execute();

      return $Qcustomer->valueInt('customer_guest_account');
    }

    /**
     * @param int $id
     */
    public function setCountryID(int $id)
    {
      $this->_data['country_id'] = $id;
    }

    /**
     * @param int $id
     */
    public function setZoneID(int $id)
    {
      $this->_data['zone_id'] = $id;
    }

    /**
     * B2B
     * @param int $id
     */
    public function setCustomersgroupID(int $id)
    {
      $this->_data1['customers_group_id'] = $id;
    }

    /**
     *
     */
    public function reset()
    {
      $this->_is_logged_on = false;
      $this->_data = [];

      if (isset($_SESSION['customer'])) {
        unset($_SESSION['customer']);
      }

// B2B
      if (isset($_SESSION['customer_group_id'])) {
        unset($_SESSION['customer_group_id']);
      }
    }


    /**
     * Customers Greeting
     * @return string $greeting_string
     */
    public function customerGreeting(): string
    {
      if ($this->isLoggedOn()) {
        $greeting_string = CLICSHOPPING::getDef('text_greeting_personal', ['first_name' => HTML::outputProtected($this->getFirstName()),
            'url_products_new' => CLICSHOPPING::link(null, 'Products&ProductsNew'),
            'url_logoff' => CLICSHOPPING::link(null, 'Account&LogOff')
          ]
        );
      } else {
        if (MODE_MANAGEMENT_B2C_B2B == 'B2C_B2B' || MODE_MANAGEMENT_B2C_B2B == 'B2B') {
          $greeting_string = CLICSHOPPING::getDef('text_greeting_guest', ['url_login' => CLICSHOPPING::redirect(null, 'Account&LogIn'),
              'url_create_account' => CLICSHOPPING::link(null, 'Account&Create'),
              'url_create_account_pro' => CLICSHOPPING::link('Account.php', 'Account&CreatePro')
            ]
          );
        } else {
          $greeting_string = CLICSHOPPING::getDef('text_greeting_guest', ['url_login' => CLICSHOPPING::redirect(null, 'Account&LogIn'),
              'url_products_new' => CLICSHOPPING::link(null, 'Products&ProductsNew')
            ]
          );
        }
      }

      return $greeting_string;
    }

    /**
     * @return bool
     */
    public function hasProductNotifications(): bool
    {
      $Qcheck = $this->db->prepare('select products_id
                                    from :table_products_notifications
                                    where customers_id = :customers_id
                                    limit 1
                                    ');
      $Qcheck->bindInt(':customers_id', $this->_data['id']);
      $Qcheck->execute();

      return ($Qcheck->fetch() !== false);
    }

    /**
     * @return mixed
     */
    public function getProductNotifications()
    {
      $CLICSHOPPING_Language = Registry::get('Language');

      $Qproducts = $this->db->prepare('select pd.products_id,
                                             pd.products_name
                                      from :table_products_description pd,
                                           :table_products_notifications pn
                                      where pn.customers_id = :customers_id
                                        and pn.products_id = pd.products_id
                                        and pd.language_id = :language_id
                                      order by pd.products_name
                                      ');
      $Qproducts->bindInt(':customers_id', $this->_data['id']);
      $Qproducts->bindInt(':language_id', $CLICSHOPPING_Language->getID());
      $Qproducts->execute();

      return $Qproducts;
    }
  }