<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Tools\AdministratorMenu\Classes\ClicShoppingAdmin;

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\Cache;

  class AdministratorMenu
  {
    protected int $category_id;
    protected int $language_id;

    /**
     *  Return catagories path
     *
     * @param string $current_category_id
     * @return string $cPath_new,
     *
     */
    public static function getPath(string $current_category_id = ''): string
    {
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_CategoriesAdmin = Registry::get('CategoriesAdmin');

      $cPath_array = $CLICSHOPPING_CategoriesAdmin->getPathArray();

      if ($current_category_id == '') {
        $cPath_new = implode('_', $cPath_array);
      } else {
        if (\count($cPath_array) == 0) {
          $cPath_new = $current_category_id;
        } else {
          $cPath_new = '';

          $Qlast = $CLICSHOPPING_Db->get('administrator_menu', 'parent_id', ['id' => (int)$cPath_array[(\count($cPath_array) - 1)]]);

          $Qcurrent = $CLICSHOPPING_Db->get('administrator_menu', 'parent_id', ['id' => (int)$current_category_id]);

          if ($Qlast->valueInt('parent_id') === $Qcurrent->valueInt('parent_id')) {
            for ($i = 0, $n = \count($cPath_array) - 1; $i < $n; $i++) {
              $cPath_new .= '_' . $cPath_array[$i];
            }
          } else {
            for ($i = 0, $n = \count($cPath_array); $i < $n; $i++) {
              $cPath_new .= '_' . $cPath_array[$i];
            }
          }

          $cPath_new .= '_' . $current_category_id;

          if (substr($cPath_new, 0, 1) == '_') {
            $cPath_new = substr($cPath_new, 1);
          }
        }
      }

      return 'cPath=' . $cPath_new;
    }

    /**
     * the category name
     *
     * @param string|null $id
     * @param int $language_id
     * @return string $category['categories_name'],  name of the categorie
     */
    public static function getAdministratorMenuLabel(?string $id, int $language_id): string
    {
      $CLICSHOPPING_Language = Registry::get('Language');

      if (!$language_id) $language_id = $CLICSHOPPING_Language->getId();
      $Qcategory = Registry::get('Db')->get('administrator_menu_description', 'label', ['id' => (int)$id, 'language_id' => (int)$language_id]);

      return $Qcategory->value('label');
    }

    /**
     *  remove category
     *
     * @param string $category_id
     * @return string
     *
     */
    public static function removeCategory(int $id)
    {
      $CLICSHOPPING_Db = Registry::get('Db');

      $CLICSHOPPING_Db->delete('administrator_menu', ['id' => (int)$id]);
      $CLICSHOPPING_Db->delete('administrator_menu_description', ['id' => (int)$id]);

      Cache::clear('menu-administrator');
    }

    /**
     * category tree
     * @param int|string $parent_id
     * @param string $spacing
     * @param array|string $exclude
     * @param array|string $category_tree_array
     * @param bool $include_itself
     * @return array
     */
    public static function getLabelTree(int|string $parent_id = '0', string $spacing = '', array|string $exclude = '', array|string $category_tree_array = '', bool $include_itself = false): array
    {
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_Language = Registry::get('Language');
      $CLICSHOPPING_AdministratorMenu = Registry::get('AdministratorMenu');

      if (!\is_array($category_tree_array)) {
        $category_tree_array = [];
      }

      if ((\count($category_tree_array) < 1) && ($exclude != '0')) {
        $category_tree_array[] = [
          'id' => '0',
          'text' => $CLICSHOPPING_AdministratorMenu->getDef('text_top')
        ];
      }

      if ($include_itself) {
        $Qcategory = $CLICSHOPPING_Db->get('administrator_menu_description', 'label', ['language_id' => (int)$CLICSHOPPING_Language->getId(),
            'id' => (int)$parent_id
          ]
        );

        $category_tree_array[] = [
          'id' => $parent_id,
          'text' => $Qcategory->value('label')
        ];
      }


      $Qcategories = $CLICSHOPPING_Db->get([
        'administrator_menu c',
        'administrator_menu_description cd'
      ], [
        'c.id',
        'cd.label',
        'c.parent_id'
      ], [
        'c.id' => [
          'rel' => 'cd.id'
        ],
        'cd.language_id' => (int)$CLICSHOPPING_Language->getId(),
        'c.parent_id' => (int)$parent_id
      ], [
          'c.sort_order',
          'cd.label'
        ]
      );

      while ($Qcategories->fetch()) {
        if ($exclude != $Qcategories->valueInt('id'))
          $category_tree_array[] = [
            'id' => $Qcategories->valueInt('id'),
            'text' => $spacing . $Qcategories->value('label')
          ];

        $category_tree_array = static::getLabelTree($Qcategories->valueInt('id'), $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array);
      }

      return $category_tree_array;
    }

    /**
     * getGeneratedAdministratorMenuPathIds
     *
     * @param int $id
     * @return string $calculated_category_path_string
     *
     */

    public static function getGeneratedAdministratorMenuPathIds(int $id)
    {
      $CLICSHOPPING_AdministratorMenu = Registry::get('AdministratorMenu');

      $calculated_category_path_string = '';
      $calculated_category_path = static::getGenerateCategoryPath($id);

      for ($i = 0, $n = \count($calculated_category_path); $i < $n; $i++) {
        for ($j = 0, $k = \count($calculated_category_path[$i]); $j < $k; $j++) {
          $calculated_category_path_string .= $calculated_category_path[$i][$j]['id'] . '_';
        }
        $calculated_category_path_string = substr($calculated_category_path_string, 0, -1) . '<br />';
      }

      $calculated_category_path_string = substr($calculated_category_path_string, 0, -6);

      if (\strlen($calculated_category_path_string) < 1) $calculated_category_path_string = $CLICSHOPPING_AdministratorMenu->getDef('text_top');

      return $calculated_category_path_string;
    }

    /**
     * @param int $id
     * @param string $categories_array
     * @param int $index
     * @return array
     */
    public static function getGenerateCategoryPath(int $id, $categories_array = '', int $index = 0): array
    {
      $CLICSHOPPING_Language = Registry::get('Language');
      $CLICSHOPPING_Db = Registry::get('Db');

      if (!\is_array($categories_array)) {
        $categories_array = [];
      }

      $Qcategory = $CLICSHOPPING_Db->get([
        'administrator_menu c',
        'administrator_menu_description cd'
      ], [
        'cd.label',
        'c.parent_id'
      ], [
          'c.id' => [
            'val' => (int)$id,
            'rel' => 'cd.id'
          ],
          'cd.language_id' => (int)$CLICSHOPPING_Language->getId()
        ]
      );

      $categories_array[$index][] = [
        'id' => (int)$id,
        'text' => $Qcategory->value('label')
      ];

      if ((!\is_null($Qcategory->valueInt('parent_id'))) && ($Qcategory->valueInt('parent_id') != '0')) {
        $categories_array = static::getGenerateCategoryPath($Qcategory->valueInt('parent_id'), $categories_array, $index);
      }

      return $categories_array;
    }

    /**
     * @param int $id
     */
    public static function getRemoveAdministratorMenuCategory(int $id)
    {
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_Template = Registry::get('TemplateAdmin');

      $QImage = $CLICSHOPPING_Db->prepare('select image
                                            from :table_administrator_menu
                                            where id = :id
                                           ');
      $QImage->bindInt(':id', (int)$id);
      $QImage->execute();

// Controle si l'image est utilise sur une autre categorie
      $QduplicateImage = $CLICSHOPPING_Db->prepare('select count(*) as total
                                                    from :table_administrator_menu
                                                    where image = :image
                                                   ');
      $QduplicateImage->bindValue(':image', $QImage->value('image'));
      $QduplicateImage->execute();

// Controle si l'image est utilise sur une autre categorie
      $QduplicateImageCategories = $CLICSHOPPING_Db->prepare('select count(*) as total
                                                              from :table_administrator_menu
                                                              where image = :image
                                                             ');
      $QduplicateImageCategories->bindValue(':image', $QImage->value('image'));
      $QduplicateImageCategories->execute();

      if (($QduplicateImage->valueInt('total') < 2) && ($QduplicateImageCategories->valueInt('total') == 0)) {
// delete categorie image
        if (is_file($CLICSHOPPING_Template->getDirectoryPathTemplateShopImages() . $QImage->value('image'))) {
          unlink($CLICSHOPPING_Template->getDirectoryPathTemplateShopImages() . $QImage->value('image'));
        }
      }

      $Qdelete = $CLICSHOPPING_Db->prepare('delete
                                              from :table_administrator_menu
                                              where id = :id
                                            ');
      $Qdelete->bindInt(':id', (int)$id);
      $Qdelete->execute();

      $Qdelete = $CLICSHOPPING_Db->prepare('delete
                                      from :table_administrator_menu_description
                                      where id = :id
                                    ');
      $Qdelete->bindInt(':id', (int)$id);
      $Qdelete->execute();
    }

    /**
     * @param string $parent_id
     * @param string $spacing
     * @param string $exclude
     * @param string $category_tree_array
     * @param bool $include_itself
     * @return array
     */
    public static function getAdministratorMenuCategoryTree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', bool $include_itself = false): array
    {
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_Language = Registry::get('Language');
      $CLICSHOPPING_AdministratorMenu = Registry::get('AdministratorMenu');

      if (!\is_array($category_tree_array)) {
        $category_tree_array = [];
      }

      if ((\count($category_tree_array) < 1) && ($exclude != '0')) {
        $category_tree_array[] = ['id' => '0', 'text' => $CLICSHOPPING_AdministratorMenu->getDef('text_top')];
      }

      if ($include_itself) {
        $Qcategory = $CLICSHOPPING_Db->prepare('select label
                                                from :table_administrator_menu_description
                                                where language_id = :language_id
                                                and id = :parent_id
                                               ');

        $Qcategory->bindInt(':language_id', (int)$CLICSHOPPING_Language->getId());
        $Qcategory->bindInt(':parent_id', (int)$parent_id);
        $Qcategory->execute();

        $category_tree_array[] = ['id' => $parent_id, 'text' => $Qcategory->value('label')];
      }

      $Qcategory = $CLICSHOPPING_Db->prepare('select c.id,
                                                       cd.label,
                                                       c.parent_id
                                                from :table_administrator_menu c,
                                                     :table_administrator_menu_description cd
                                                where c.id = cd.id
                                                and cd.language_id = :language_id
                                                and c.parent_id = :parent_id
                                                order by c.sort_order,
                                                         cd.label
                                              ');

      $Qcategory->bindInt(':language_id', (int)$CLICSHOPPING_Language->getId());
      $Qcategory->bindInt(':parent_id', (int)$parent_id);
      $Qcategory->execute();

      while ($Qcategory->fetch()) {
        if ($exclude != $Qcategory->valueInt('id')) {
          $category_tree_array[] = [
            'id' => $Qcategory->valueInt('id'),
            'text' => $spacing . $Qcategory->value('label')
          ];
        }

        $category_tree_array = static::getAdministratorMenuCategoryTree($Qcategory->valueInt('id'), $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array);
      }

      return $category_tree_array;
    }

    /**
     * Count how many subcategories exist in a category
     * @param int $id
     * @return int
     */
    public static function getChildsInMenuCount(int $id): int
    {
      $CLICSHOPPING_Db = Registry::get('Db');

      $categories_count = 0;

      $Qcategories = $CLICSHOPPING_Db->prepare('select id
                                                from :table_administrator_menu
                                                where parent_id = :parent_id
                                                ');

      $Qcategories->bindInt(':parent_id', $id);
      $Qcategories->execute();

      while ($Qcategories->fetch() !== false) {
        $categories_count++;

        $categories_count += \call_user_func(__METHOD__, $Qcategories->valueInt('id'));
      }

      return $categories_count;
    }
  
    /*
     * Display the headermenu
     * @return array
     */
    public static function getHeaderMenu() :array
    {
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_Language = Registry::get('Language');
  
      if (isset($_SESSION['admin']['access'])) {
        if ($_SESSION['admin']['access'] === 1) {
          $access_level = 0;
        } elseif ($_SESSION['admin']['access'] === 2) {
          $access_level = 2;
        } elseif ($_SESSION['admin']['access'] === 3) {
          $access_level = 2;
        } else {
          $access_level = 0;
        }
      } else {
        $access_level = 0;
      }
  
      if ($access_level == 0) {
        $Qmenus = $CLICSHOPPING_Db->prepare('select am.id,
                                                    am.link,
                                                    am.parent_id,
                                                    am.access,
                                                    am.sort_order,
                                                    am.image,
                                                    am.b2b_menu,
                                                    amd.label,
                                                    ad.access
                                              from :table_administrator_menu am  left join :table_administrators ad on ad.access =  am.access,
                                                  :table_administrator_menu_description amd
                                              where am.id = amd.id
                                              and amd.language_id = :language_id
                                              order by am.parent_id,
                                                       am.sort_order
                                            ');
        $Qmenus->bindInt(':language_id', $CLICSHOPPING_Language->getId());
    
      } elseif ($access_level == 2) {
        $Qmenus = $CLICSHOPPING_Db->prepare('select am.id,
                                                  am.link,
                                                  am.parent_id,
                                                  am.access,
                                                  am.sort_order,
                                                  am.image,
                                                  am.b2b_menu,
                                                  amd.label,
                                                  ad.access
                                            from :table_administrator_menu am  left join :table_administrators ad on ad.access =  am.access,
                                                :table_administrator_menu_description amd
                                            where am.id = amd.id
                                            and amd.language_id = :language_id
                                            and (am.access = 0 or am.access > 1)
                                            order by am.parent_id,
                                                     am.sort_order
                                          ');
    
        $Qmenus->bindInt(':language_id', $CLICSHOPPING_Language->getId());
    
      } elseif ($access_level == 3) {
        $Qmenus = $CLICSHOPPING_Db->prepare('select am.id,
                                                am.link,
                                                  am.parent_id,
                                                  am.access,
                                                  am.sort_order,
                                                  am.image,
                                                  am.b2b_menu,
                                                  amd.label,
                                                  ad.access
                                            from :table_administrator_menu am  left join :table_administrators ad on ad.access =  am.access,
                                                :table_administrator_menu_description amd
                                            where am.id = amd.id
                                            and amd.language_id = :language_id
                                            and (am.access = 0 and am.access > 2)
                                            order by am.parent_id,
                                                     am.sort_order
                                          ');
    
        $Qmenus->bindInt(':language_id', $CLICSHOPPING_Language->getId());
      }
  
      $Qmenus->setCache('menu-administrator');
      $Qmenus->execute();
      
      return $Qmenus->fetchAll();
    }
  }
