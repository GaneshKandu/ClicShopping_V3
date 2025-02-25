<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Catalog\Categories\Sites\ClicShoppingAdmin\Pages\Home\Actions;

  use ClicShopping\OM\Registry;

  class Move extends \ClicShopping\OM\PagesActionsAbstract
  {
    public function execute()
    {
      $CLICSHOPPING_Categories = Registry::get('Categories');

      $this->page->setFile('move.php');
      $this->page->data['action'] = 'MoveConfirm';

      $CLICSHOPPING_Categories->loadDefinitions('Sites/ClicShoppingAdmin/Categories');
    }
  }