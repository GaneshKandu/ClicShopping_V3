<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Orders\Orders\Sites\ClicShoppingAdmin\Pages\Home\Actions;

  use ClicShopping\OM\Registry;

  class PageManagerOrderHistoryContract extends \ClicShopping\OM\PagesActionsAbstract
  {
    protected $use_site_template = false;

    public function execute()
    {
      $CLICSHOPPING_Orders = Registry::get('Orders');

      $this->page->setUseSiteTemplate(false); //don't display Header / Footer
      $this->page->setFile('page_manager_order_contract.php');

      $CLICSHOPPING_Orders->loadDefinitions('Sites/ClicShoppingAdmin/main');
    }
  }