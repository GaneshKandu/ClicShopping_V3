<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Customers\Customers\Sites\ClicShoppingAdmin\Pages\Home\Actions\Configure;

  use ClicShopping\OM\Registry;

  class Process extends \ClicShopping\OM\PagesActionsAbstract
  {
    public function execute()
    {
      $CLICSHOPPING_MessageStack = Registry::get('MessageStack');
      $CLICSHOPPING_Customers = Registry::get('Customers');

      $current_module = $this->page->data['current_module'];

      $m = Registry::get('CustomersAdminConfig' . $current_module);

      foreach ($m->getParameters() as $key) {
        $p = mb_strtolower($key);

        if (isset($_POST[$p])) {
          $CLICSHOPPING_Customers->saveCfgParam($key, $_POST[$p]);
        }
      }

      $CLICSHOPPING_MessageStack->add($CLICSHOPPING_Customers->getDef('alert_cfg_saved_success'), 'success');

      $CLICSHOPPING_Customers->redirect('Configure&module=' . $current_module);
    }
  }
