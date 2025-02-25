<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Configuration\Api\Sites\Shop\Pages\Orders;

  use ClicShopping\Apps\Configuration\Api\Classes\Shop\ApiShop;
  use ClicShopping\OM\Registry;
  use ClicShopping\OM\HTML;

  class Orders extends \ClicShopping\OM\PagesAbstract
  {
    protected $file = null;
    protected bool $use_site_template = false;
    protected mixed $lang;
    protected mixed $Db;

    protected function init()
    {
      $this->lang = Registry::get('Language');
      $this->Db = Registry::get('Db');

      if (!\defined('CLICSHOPPING_APP_API_AI_STATUS') && CLICSHOPPING_APP_API_AI_STATUS == 'False') {
        return false;
      }

      $requestMethod = ApiShop::requestMethod();

// Handle the event
      switch ($requestMethod) {
        case 'GET':
          $token = HTML::sanitize($_GET['token']);
          $result = ApiShop::checkToken($token);
          $check = $this->statusCheck('get_order_status', $token);

          if (empty($result) || $check == 0) {
            $response = ApiShop::notFoundResponse();
            Registry::get('Session')->kill();
          } else {
            $response = static::getOrder();
          }
          break;
        case 'DELETE':
          $token = HTML::sanitize($_GET['token']);
          $result = ApiShop::checkToken($token);

          $check = $this->statusCheck('delete_order_status', $token);

          if (empty($result)  || $check == 0) {
            $response = ApiShop::notFoundResponse();
            Registry::get('Session')->kill();
          } else {
            $response = static::deleteOrder();
          }
        break;
        case 'POST':
          $token = HTML::sanitize($_GET['token']);
          $result = ApiShop::checkToken($token);

          if (isset($_GET['update'])) {
            $check = $this->statusCheck('update_order_status', $token);

            if (empty($result)  || $check == 0) {
              $response = ApiShop::notFoundResponse();
              Registry::get('Session')->kill();
            } else {
              $response = static::saveOrder();  //@todo
            }
          } elseif (isset($_GET['update'])) {
            $check = $this->statusCheck('insert_order_status', $token);

            if (empty($result) || $check == 0) {
              $response = ApiShop::notFoundResponse();
              Registry::get('Session')->kill();
            } else {
              $response = static::saveOrder();  //@todo
            }
          }
          break;
        case 'PUT':
          break;
        default:
          $response = ApiShop::notFoundResponse();
          Registry::get('Session')->kill();
          break;
      }

      if ($response['body']) {
        echo $response['body'];
      }

      exit;
    }

    /**
     * @return array
     */
    private static function getOrder(): array
    {
      $CLICSHOPPING_Hooks = Registry::get('Hooks');

      $result = $CLICSHOPPING_Hooks->call('Api', 'ApiGetOrder');

      if (empty($result)) {
        $response = ApiShop::notFoundResponse();
      } else {
       $response = ApiShop::HttpResponseOk($result);
      }

      ApiShop::clearCache();

      return $response;
    }

    /**
     * @return array
     */
    private static function deleteOrder(): array
    {
      $CLICSHOPPING_Hooks = Registry::get('Hooks');

      $result = $CLICSHOPPING_Hooks->call('Api', 'ApiDeleteOrder');

      if (empty($result)) {
        $response = ApiShop::notFoundResponse();
      } else {
        $response = ApiShop::HttpResponseOk($result);
      }

      ApiShop::clearCache();

      return $response;
    }

    /**
     * @return array
     */
    private static function saveOrder(): array
    {
      $CLICSHOPPING_Hooks = Registry::get('Hooks');

      $result = $CLICSHOPPING_Hooks->call('Api', 'ApiSaveOrder');

      if (empty($result)) {
        $response = ApiShop::notFoundResponse();
      } else {
        $response = ApiShop::HttpResponseOk($result);
      }

      ApiShop::clearCache();

      return $response;
    }

    /**
     * @param string $string
     * @param string $token
     * @return int
     */
    private function statusCheck(string $string, string $token) :int
    {
      $QstatusCheck = $this->Db->prepare('select a.' . $string . '
                                          from :table_api a,
                                               :table_api_session ase
                                          where a.api_id = ase.api_id
                                          and ase.session_id = :session_id  
                                        ');
      $QstatusCheck->bindValue('session_id', $token);

      $QstatusCheck->execute();

      $result = $QstatusCheck->valueInt($string);

      return $result;
    }
  }
