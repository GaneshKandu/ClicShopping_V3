<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Service\Shop;

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\CLICSHOPPING;

  use ClicShopping\Apps\Orders\Orders\Classes\Shop\Order as OrderClass;

  class Order implements \ClicShopping\OM\ServiceInterface
  {
    public static function start(): bool
    {
      if (is_file(CLICSHOPPING::BASE_DIR . 'Apps/Orders/Orders/Classes/Shop/Order.php')) {
        Registry::set('Order', new OrderClass());
        return true;
      } else {
        return false;
      }
    }

    public static function stop(): bool
    {
      return true;
    }
  }
