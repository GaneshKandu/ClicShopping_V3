<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */


  namespace ClicShopping\Apps\Report\StatsProductsNotification\Sites\ClicShoppingAdmin\Pages\Home\Actions\StatsProductsNotification;

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\HTML;

  class Update extends \ClicShopping\OM\PagesActionsAbstract
  {

    public function execute()
    {

      $CLICSHOPPING_StatsProductsNotification = Registry::get('StatsProductsNotification');

      if (isset($_GET['resetViewed'])) $resetViewed = HTML::sanitize($_GET['resetViewed']);
      if (isset($_GET['products_id'])) $products_id = HTML::sanitize($_GET['products_id']);

      if ($resetViewed == '0') {
// Reset ALL counts
        $Qupdate = $CLICSHOPPING_StatsProductsNotification->db->prepare('update :table_products_description
                                                                  set products_viewed = 0
                                                                  where 1
                                                                ');
        $Qupdate->execute();

      } else {
// Reset selected product count
        $Qupdate = $CLICSHOPPING_StatsProductsNotification->db->prepare('update :table_products_description
                                                                  set products_viewed = 0
                                                                  where products_id = :products_id
                                                                ');
        $Qupdate->bindInt(':products_id', (int)$products_id);
        $Qupdate->execute();
      }
  }
  }
