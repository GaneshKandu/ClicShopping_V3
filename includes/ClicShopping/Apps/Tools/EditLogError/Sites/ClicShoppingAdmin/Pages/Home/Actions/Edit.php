<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Tools\EditLogError\Sites\ClicShoppingAdmin\Pages\Home\Actions;

  use ClicShopping\OM\Registry;

  class Edit extends \ClicShopping\OM\PagesActionsAbstract
  {
    public function execute()
    {
      $CLICSHOPPING_EditLogError = Registry::get('EditLogError');

      $this->page->setFile('edit.php');
      $this->page->data['action'] = 'LogError';

      $CLICSHOPPING_EditLogError->loadDefinitions('Sites/ClicShoppingAdmin/main');
    }
  }