<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Catalog\Products\Sites\ClicShoppingAdmin\Pages\Home\Actions\Configure;

  use ClicShopping\OM\Registry;

  class Process extends \ClicShopping\OM\PagesActionsAbstract
  {
    public function execute()
    {
      $CLICSHOPPING_MessageStack = Registry::get('MessageStack');
      $CLICSHOPPING_Products = Registry::get('Products');

      $current_module = $this->page->data['current_module'];

      $m = Registry::get('ProductsAdminConfig' . $current_module);

      foreach ($m->getParameters() as $key) {
        $p = mb_strtolower($key);

        if (isset($_POST[$p])) {
          $CLICSHOPPING_Products->saveCfgParam($key, $_POST[$p]);
        }
      }

      $CLICSHOPPING_MessageStack->add($CLICSHOPPING_Products->getDef('alert_cfg_saved_success'), 'success');

      $CLICSHOPPING_Products->redirect('Configure&module=' . $current_module);
    }
  }
