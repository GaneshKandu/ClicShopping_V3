<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */


  namespace ClicShopping\Apps\OrderTotal\TotalTax\Sites\ClicShoppingAdmin\Pages\Home\Actions\Configure;

  use ClicShopping\OM\Registry;

  use ClicShopping\OM\Cache;
  use ClicShopping\OM\CLICSHOPPING;

  class Install extends \ClicShopping\OM\PagesActionsAbstract
  {

    public function execute()
    {

      $CLICSHOPPING_MessageStack = Registry::get('MessageStack');
      $CLICSHOPPING_TotalTax = Registry::get('TotalTax');

      $current_module = $this->page->data['current_module'];

      $CLICSHOPPING_TotalTax->loadDefinitions('Sites/ClicShoppingAdmin/install');

      $m = Registry::get('TotalTaxAdminConfig' . $current_module);
      $m->install();

      static::installDbMenuAdministration();

      $CLICSHOPPING_MessageStack->add($CLICSHOPPING_TotalTax->getDef('alert_module_install_success'), 'success', 'TotalTax');

      $CLICSHOPPING_TotalTax->redirect('Configure&module=' . $current_module);
    }

    private static function installDbMenuAdministration()
    {
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_TotalTax = Registry::get('TotalTax');
      $CLICSHOPPING_Language = Registry::get('Language');

      $Qcheck = $CLICSHOPPING_Db->get('administrator_menu', 'app_code', ['app_code' => 'app_order_total_tax']);

      if ($Qcheck->fetch() === false) {

        $sql_data_array = ['sort_order' => 7,
          'link' => 'index.php?A&OrderTotal\TotalTax&Configure',
          'image' => 'modules_order_total.gif',
          'b2b_menu' => 0,
          'access' => 1,
          'app_code' => 'app_order_total_tax'
        ];

        $insert_sql_data = ['parent_id' => 451];

        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

        $CLICSHOPPING_Db->save('administrator_menu', $sql_data_array);

        $id = $CLICSHOPPING_Db->lastInsertId();

        $languages = $CLICSHOPPING_Language->getLanguages();

        for ($i = 0, $n = \count($languages); $i < $n; $i++) {

          $language_id = $languages[$i]['id'];

          $sql_data_array = ['label' => $CLICSHOPPING_TotalTax->getDef('title_menu')];

          $insert_sql_data = [
            'id' => (int)$id,
            'language_id' => (int)$language_id
          ];

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          $CLICSHOPPING_Db->save('administrator_menu_description', $sql_data_array);

        }

        Cache::clear('menu-administrator');
      }
    }
  }
