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

  namespace ClicShopping\Apps\Configuration\OrdersStatus;

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\CLICSHOPPING;

  class OrdersStatus extends \ClicShopping\OM\AppAbstract
  {

    protected $api_version = 1;
    protected string $identifier = 'ClicShopping_OrdersStatus_V1';

    protected function init()
    {
    }

    /**
     * @return array|mixed
     */
    public function getConfigModules(): mixed
    {
      static $result;

      if (!isset($result)) {
        $result = [];

        $directory = CLICSHOPPING::BASE_DIR . 'Apps/Configuration/OrdersStatus/Module/ClicShoppingAdmin/Config';
        $name_space_config = 'ClicShopping\Apps\Configuration\OrdersStatus\Module\ClicShoppingAdmin\Config';
        $trigger_message = 'ClicShopping\Apps\Configuration\OrdersStatus\OrdersStatus::getConfigModules(): ';

        $this->getConfigApps($result, $directory, $name_space_config, $trigger_message);
      }

      return $result;
    }

    public function getConfigModuleInfo($module, $info)
    {
      if (!Registry::exists('OrdersStatusAdminConfig' . $module)) {
        $class = 'ClicShopping\Apps\Configuration\OrdersStatus\Module\ClicShoppingAdmin\Config\\' . $module . '\\' . $module;

        Registry::set('OrdersStatusAdminConfig' . $module, new $class);
      }

      return Registry::get('OrdersStatusAdminConfig' . $module)->$info;
    }


    public function getApiVersion()
    {
      return $this->api_version;
    }

     /**
     * @return string
     */
    public function getIdentifier() :String
    {
      return $this->identifier;
    }
  }
