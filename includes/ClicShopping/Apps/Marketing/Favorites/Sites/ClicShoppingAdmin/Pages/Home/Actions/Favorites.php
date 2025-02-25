<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Marketing\Favorites\Sites\ClicShoppingAdmin\Pages\Home\Actions;

  use ClicShopping\OM\Registry;

  class Favorites extends \ClicShopping\OM\PagesActionsAbstract
  {
    public function execute()
    {
      $CLICSHOPPING_Favorites = Registry::get('Favorites');

      $this->page->setFile('favorites.php');
      $this->page->data['action'] = 'Favorites';

      $CLICSHOPPING_Favorites->loadDefinitions('Sites/ClicShoppingAdmin/Favorites');
    }
  }