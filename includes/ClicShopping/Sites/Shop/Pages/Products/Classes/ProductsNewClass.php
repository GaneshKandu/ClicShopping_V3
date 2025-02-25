<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Sites\Shop\Pages\Products\Classes;

  use ClicShopping\OM\Registry;

  class ProductsNewClass
  {
    /**
     * @return array
     */
    public static function getCountColumnList()
    {
// create column list
      $define_list = [
        'MODULE_PRODUCTS_NEW_LIST_DATE_ADDED' => MODULE_PRODUCTS_NEW_LIST_DATE_ADDED,
        'MODULE_PRODUCTS_NEW_LIST_PRICE' => MODULE_PRODUCTS_NEW_LIST_PRICE,
        'MODULE_PRODUCTS_NEW_LIST_MODEL' => MODULE_PRODUCTS_NEW_LIST_MODEL,
        'MODULE_PRODUCTS_NEW_LIST_WEIGHT' => MODULE_PRODUCTS_NEW_LIST_WEIGHT,
        'MODULE_PRODUCTS_NEW_LIST_QUANTITY' => MODULE_PRODUCTS_NEW_LIST_QUANTITY,
      ];

      asort($define_list);

      $column_list = [];

      foreach ($define_list as $key => $value) {
        if ($value > 0) $column_list[] = $key;
      }

      return $column_list;
    }

    /**
     * @return string
     */
    private static function Listing()
    {
      $CLICSHOPPING_Customer = Registry::get('Customer');

      $Qlisting = 'select SQL_CALC_FOUND_ROWS ';

      $count_column = static::getCountColumnList();

      for ($i = 0, $n = \count($count_column); $i < $n; $i++) {
        switch ($count_column[$i]) {
          case 'MODULE_PRODUCTS_NEW_LIST_DATE_ADDED':
            $Qlisting .= ' p.products_date_added, ';
            break;
          case 'MODULE_PRODUCTS_NEW_LIST_PRICE':
            $Qlisting .= ' p.products_price, ';
            break;
          case 'MODULE_PRODUCTS_NEW_LIST_MODEL':
            $Qlisting .= ' p.products_model, ';
            break;
          case 'MODULE_PRODUCTS_NEW_LIST_WEIGHT':
            $Qlisting .= ' p.products_weight, ';
            break;
          case 'MODULE_PRODUCTS_NEW_LIST_QUANTITY':
            $Qlisting .= ' p.products_quantity, ';
            break;
        }
      }

      if ($CLICSHOPPING_Customer->getCustomersGroupID() != 0) {
        $Qlisting .= ' p.products_id,
                       p.products_quantity as in_stock,
                       g.customers_group_price,
                       g.price_group_view,
                       g.orders_group_view
                        from :table_products p left join :table_products_groups g on p.products_id = g.products_id,
                             :table_products_to_categories p2c,
                             :table_categories c
                        where p.products_status = 1
                        and g.customers_group_id = :customers_group_id
                        and g.products_group_view = 1
                        and p.products_archive = 0
                        and p.products_id = p2c.products_id
                        and p2c.categories_id = c.categories_id
                        and c.status = 1
                       ';

      } else {
        $Qlisting .= ' p.products_id,
                       p.products_quantity as in_stock
                        from :table_products p,
                             :table_products_to_categories p2c,
                             :table_categories c
                        where p.products_status = 1
                        and p.products_view = 1
                        and p.products_archive = 0
                        and p.products_id = p2c.products_id
                        and p2c.categories_id = c.categories_id
                        and c.status = 1
                       ';
      }

      if ((!isset($_GET['sort'])) || (!preg_match('/^[1-8][ad]$/', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > \count($count_column))) {
        for ($i = 0, $n = \count($count_column); $i < $n; $i++) {
          if ($count_column[$i] == 'MODULE_PRODUCTS_NEW_LIST_DATE_ADDED') {
            $_GET['sort'] = $i + 1 . 'a';
            $Qlisting .= ' order by p.products_date_added DESC ';
            break;
          }
        }
      } else {

        $sort_col = substr($_GET['sort'], 0, 1);
        $sort_order = substr($_GET['sort'], 1);

        switch ($count_column[$sort_col - 1]) {
          case 'MODULE_PRODUCTS_NEW_LIST_DATE_ADDED':
            $Qlisting .= ' order by p.products_date_added ' . ($sort_order == 'd' ? 'desc' : ' ');
            break;
          case 'MODULE_PRODUCTS_NEW_LIST_PRICE':
            $Qlisting .= ' order by p.products_price ' . ($sort_order == 'd' ? 'desc' : '') . ', p.products_date_added DESC ';
            break;
          case 'MODULE_PRODUCTS_NEW_LIST_MODEL':
            $Qlisting .= ' order by p.products_model ' . ($sort_order == 'd' ? 'desc' : '') . ', p.products_date_added DESC ';
            break;
          case 'MODULE_PRODUCTS_NEW_LIST_QUANTITY':
            $Qlisting .= ' order by p.products_quantity ' . ($sort_order == 'd' ? 'desc' : '') . ', p.products_date_added DESC ';
            break;
          case 'MODULE_PRODUCTS_NEW_LIST_QUANTITY':
            $Qlisting .= ' order by p.products_weight ' . ($sort_order == 'd' ? 'desc' : '') . ', p.products_date_added DESC ';
            break;
        }
      }

      $Qlisting .= ' limit :page_set_offset,
                               :page_set_max_results
                       ';

      return $Qlisting;
    }

    /**
     * @return mixed
     */
    public static function getListing()
    {
      $CLICSHOPPING_Customer = Registry::get('Customer');
      $CLICSHOPPING_Db = Registry::get('Db');

      $Qlisting = static::Listing();

      if ($CLICSHOPPING_Customer->getCustomersGroupID() != 0) {
        $QlistingProductsNews = $CLICSHOPPING_Db->prepare($Qlisting);
        $QlistingProductsNews->bindInt(':customers_group_id', (int)$CLICSHOPPING_Customer->getCustomersGroupID());
      } else {
        $QlistingProductsNews = $CLICSHOPPING_Db->prepare($Qlisting);
      }

      return $QlistingProductsNews;
    }
  }