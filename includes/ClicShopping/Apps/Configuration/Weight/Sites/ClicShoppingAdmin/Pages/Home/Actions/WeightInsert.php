<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Configuration\Weight\Sites\ClicShoppingAdmin\Pages\Home\Actions;

  use ClicShopping\OM\Registry;

  class WeightInsert extends \ClicShopping\OM\PagesActionsAbstract
  {
    public function execute()
    {
      $CLICSHOPPING_Weight = Registry::get('Weight');

      $this->page->setFile('weight_insert.php');
      $this->page->data['action'] = 'WeightInsert';

      $CLICSHOPPING_Weight->loadDefinitions('Sites/ClicShoppingAdmin/weight');
    }
  }