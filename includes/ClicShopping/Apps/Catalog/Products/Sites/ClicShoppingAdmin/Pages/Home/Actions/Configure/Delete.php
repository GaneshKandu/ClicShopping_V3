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
  use ClicShopping\OM\Cache;

  class Delete extends \ClicShopping\OM\PagesActionsAbstract
  {
    public function execute()
    {
      $CLICSHOPPING_MessageStack = Registry::get('MessageStack');
      $CLICSHOPPING_Products = Registry::get('Products');

      $current_module = $this->page->data['current_module'];
      $m = Registry::get('ProductsAdminConfig' . $current_module);
      $m->uninstall();

      static::removeMenu();
      static::removeDb();

      $CLICSHOPPING_MessageStack->add($CLICSHOPPING_Products->getDef('alert_module_uninstall_success'), 'success');

      $CLICSHOPPING_Products->redirect('Configure&module=' . $current_module);
    }

    private static function removeMenu()
    {
      $CLICSHOPPING_Db = Registry::get('Db');

      $Qcheck = $CLICSHOPPING_Db->get('administrator_menu', 'app_code', ['app_code' => 'app_catalog_products']);

      if ($Qcheck->fetch()) {
        $QMenuId = $CLICSHOPPING_Db->prepare('select id
                                              from :table_administrator_menu
                                              where app_code = :app_code
                                            ');

        $QMenuId->bindValue(':app_code', 'app_catalog_products');
        $QMenuId->execute();

        $menu = $QMenuId->fetchAll();

        $menu1 = \count($menu);

        for ($i = 0, $n = $menu1; $i < $n; $i++) {
          $CLICSHOPPING_Db->delete('administrator_menu_description', ['id' => (int)$menu[$i]['id']]);
        }

        $CLICSHOPPING_Db->delete('administrator_menu', ['app_code' => 'app_catalog_products']);
      }

      $Qcheck = $CLICSHOPPING_Db->get('administrator_menu', 'app_code', ['app_code' => 'app_report_stats_products_viewed']);

      if ($Qcheck->fetch()) {
        $QMenuId = $CLICSHOPPING_Db->prepare('select id
                                              from :table_administrator_menu
                                              where app_code = :app_code
                                            ');

        $QMenuId->bindValue(':app_code', 'app_report_stats_products_viewed');
        $QMenuId->execute();

        $menu = $QMenuId->fetchAll();

        $menu1 = \count($menu);

        for ($i = 0, $n = $menu1; $i < $n; $i++) {
          $CLICSHOPPING_Db->delete('administrator_menu_description', ['id' => (int)$menu[$i]['id']]);
        }

        $CLICSHOPPING_Db->delete('administrator_menu', ['app_code' => 'app_report_stats_products_viewed']);
      }

      $Qcheck = $CLICSHOPPING_Db->get('administrator_menu', 'app_code', ['app_code' => 'app_report_stats_low_stock']);

      if ($Qcheck->fetch()) {

        $QMenuId = $CLICSHOPPING_Db->prepare('select id
                                        from :table_administrator_menu
                                        where app_code = :app_code
                                      ');

        $QMenuId->bindValue(':app_code', 'app_report_stats_low_stock');
        $QMenuId->execute();

        $menu = $QMenuId->fetchAll();

        $menu1 = \count($menu);

        for ($i = 0, $n = $menu1; $i < $n; $i++) {
          $CLICSHOPPING_Db->delete('administrator_menu_description', ['id' => (int)$menu[$i]['id']]);
        }

        $CLICSHOPPING_Db->delete('administrator_menu', ['app_code' => 'app_report_stats_low_stock']);
      }

      $Qcheck = $CLICSHOPPING_Db->get('administrator_menu', 'app_code', ['app_code' => 'app_report_stats_products_expected']);

      if ($Qcheck->fetch()) {

        $QMenuId = $CLICSHOPPING_Db->prepare('select id
                                              from :table_administrator_menu
                                              where app_code = :app_code
                                            ');

        $QMenuId->bindValue(':app_code', 'app_report_stats_products_expected');
        $QMenuId->execute();

        $menu = $QMenuId->fetchAll();

        $menu1 = \count($menu);

        for ($i = 0, $n = $menu1; $i < $n; $i++) {
          $CLICSHOPPING_Db->delete('administrator_menu_description', ['id' => (int)$menu[$i]['id']]);
        }

        $CLICSHOPPING_Db->delete('administrator_menu', ['app_code' => 'app_report_stats_products_expected']);
      }

      $Qcheck = $CLICSHOPPING_Db->get('administrator_menu', 'app_code', ['app_code' => 'app_report_stats_products_purchased']);

      if ($Qcheck->fetch()) {
        $QMenuId = $CLICSHOPPING_Db->prepare('select id
                                              from :table_administrator_menu
                                              where app_code = :app_code
                                            ');

        $QMenuId->bindValue(':app_code', 'app_report_stats_products_purchased');
        $QMenuId->execute();

        $menu = $QMenuId->fetchAll();

        $menu1 = \count($menu);

        for ($i = 0, $n = $menu1; $i < $n; $i++) {
          $CLICSHOPPING_Db->delete('administrator_menu_description', ['id' => (int)$menu[$i]['id']]);
        }

        $CLICSHOPPING_Db->delete('administrator_menu', ['app_code' => 'app_report_stats_products_purchased']);
      }


      Cache::clear('menu-administrator');
    }

    private static function removeDb()
    {
      $CLICSHOPPING_Db = Registry::get('Db');

      $Qcheck = $CLICSHOPPING_Db->query('show tables like ":table_products"');

      if ($Qcheck->fetch() !== false) {
        $Qdelete = $CLICSHOPPING_Db->prepare('delete from :table_products');
        $Qdelete->execute();
      }

      $Qcheck = $CLICSHOPPING_Db->query('show tables like ":table_products_description"');

      if ($Qcheck->fetch() !== false) {
        $Qdelete = $CLICSHOPPING_Db->prepare('delete from :table_products_description');
        $Qdelete->execute();
      }
    }
  }