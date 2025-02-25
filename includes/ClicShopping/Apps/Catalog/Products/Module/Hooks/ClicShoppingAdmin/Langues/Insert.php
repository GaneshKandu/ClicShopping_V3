<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Catalog\Products\Module\Hooks\ClicShoppingAdmin\Langues;

  use ClicShopping\OM\Registry;

  use ClicShopping\Apps\Catalog\Products\Products as ProductsApp;
  use ClicShopping\Apps\Configuration\Langues\Classes\ClicShoppingAdmin\LanguageAdmin;

  class Insert implements \ClicShopping\OM\Modules\HooksInterface
  {
    protected mixed $app;
    protected mixed $lang;
    protected $insert_language_id;

    public function __construct()
    {
      if (!Registry::exists('Products')) {
        Registry::set('Products', new ProductsApp());
      }

      $this->app = Registry::get('Products');
      $this->lang = Registry::get('Language');
    }

    private function insert()
    {
      $insert_language_id = LanguageAdmin::getLatestLanguageID();

      $Qproducts = $this->app->db->prepare('select p.products_id as orig_product_id,
                                                   pd.*
                                            from :table_products p left join :table_products_description pd on p.products_id = pd.products_id
                                            where pd.language_id = :language_id
                                            ');

      $Qproducts->bindInt(':language_id', $this->lang->getId());
      $Qproducts->execute();

      while ($Qproducts->fetch()) {
        $cols = $Qproducts->toArray();

        $cols['products_id'] = $cols['orig_product_id'];
        $cols['language_id'] = (int)$insert_language_id;
        $cols['products_viewed'] = 0;

        unset($cols['orig_product_id']);

        $this->app->db->save('products_description', $cols);
      }
    }

    public function execute()
    {
      if (!\defined('CLICSHOPPING_APP_CATALOG_PRODUCTS_PD_STATUS') || CLICSHOPPING_APP_CATALOG_PRODUCTS_PD_STATUS == 'False') {
        return false;
      }

      if (isset($_GET['Langues'], $_GET['Insert'])) {
        $this->insert();
      }
    }
  }