<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Marketing\SEO\Sites\ClicShoppingAdmin\Pages\Home\Actions\SEO;

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\HTML;

  class Update extends \ClicShopping\OM\PagesActionsAbstract
  {
    public function execute()
    {
      $CLICSHOPPING_SEO = Registry::get('SEO');
      $CLICSHOPPING_Hooks = Registry::get('Hooks');
      $CLICSHOPPING_Language = Registry::get('Language');

      $languages = $CLICSHOPPING_Language->getLanguages();
// Language
      for ($i = 1, $n = \count($languages); $i < $n; $i++) {
        $languages_id = $i;
        $submit_defaut_language_title_h1 = HTML::sanitize($_POST['submit_defaut_language_title_h1_'][$languages[$i]['id']] ?? '');
        $submit_defaut_language_title = HTML::sanitize($_POST['submit_defaut_language_title_'][$languages[$i]['id']] ?? '');
        $submit_defaut_language_keywords = HTML::sanitize($_POST['submit_defaut_language_keywords_'][$languages[$i]['id']] ?? '');
        $submit_defaut_language_description = HTML::sanitize($_POST['submit_defaut_language_description_'][$languages[$i]['id']] ?? '');
        $submit_defaut_language_footer = HTML::sanitize($_POST['submit_defaut_language_footer_'][$languages[$i]['id']] ?? '');
        $submit_language_products_info_title = HTML::sanitize($_POST['submit_language_products_info_title_'][$languages[$i]['id']] ?? '');
        $submit_language_products_info_keywords = HTML::sanitize($_POST['submit_language_products_info_keywords_'][$languages[$i]['id']] ?? '');
        $submit_language_products_info_description = HTML::sanitize($_POST['submit_language_products_info_description_'][$languages[$i]['id']] ?? '');
        $submit_language_products_new_title = HTML::sanitize($_POST['submit_language_products_new_title_'][$languages[$i]['id']] ?? '');
        $submit_language_products_new_keywords = HTML::sanitize($_POST['submit_language_products_new_keywords_'][$languages[$i]['id']] ?? '');
        $submit_language_products_new_description = HTML::sanitize($_POST['submit_language_products_new_description_'][$languages[$i]['id']] ?? '');

        $submit_language_special_title = HTML::sanitize($_POST['submit_language_special_title_'][$languages[$i]['id']] ?? '');
        $submit_language_special_keywords = HTML::sanitize($_POST['submit_language_special_keywords_'][$languages[$i]['id']] ?? '');
        $submit_language_special_description = HTML::sanitize($_POST['submit_language_special_description_'][$languages[$i]['id']] ?? '');
        $submit_language_reviews_title = HTML::sanitize($_POST['submit_language_reviews_title_'][$languages[$i]['id']] ?? '');
        $submit_language_reviews_keywords = HTML::sanitize($_POST['submit_language_reviews_keywords_'][$languages[$i]['id']] ?? '');
        $submit_language_reviews_description = HTML::sanitize($_POST['submit_language_reviews_description_'][$languages[$i]['id']] ?? '');
        $submit_language_favorites_title = HTML::sanitize($_POST['submit_language_favorites_title_'][$languages[$i]['id']] ?? '');
        $submit_language_favorites_keywords = HTML::sanitize($_POST['submit_language_favorites_keywords_'][$languages[$i]['id']] ?? '');
        $submit_language_favorites_description = HTML::sanitize($_POST['submit_language_favorites_description_'][$languages[$i]['id']] ?? '');
        $submit_language_featured_title = HTML::sanitize($_POST['submit_language_featured_title_'][$languages[$i]['id']] ?? '');
        $submit_language_featured_keywords = HTML::sanitize($_POST['submit_language_featured_keywords_'][$languages[$i]['id']] ?? '');
        $submit_language_featured_description = HTML::sanitize($_POST['submit_language_featured_description_'][$languages[$i]['id']] ?? '');

        $sql_data_array_pages_description = [
          'submit_defaut_language_title' => $submit_defaut_language_title ,
          'submit_defaut_language_keywords' => $submit_defaut_language_keywords ,
          'submit_defaut_language_description' => $submit_defaut_language_description ,
          'submit_defaut_language_footer' => $submit_defaut_language_footer ,
          'submit_defaut_language_title_h1' =>  $submit_defaut_language_title_h1,
          'submit_language_products_info_title' => $submit_language_products_info_title ,
          'submit_language_products_info_keywords' => $submit_language_products_info_keywords ,
          'submit_language_products_info_description' => $submit_language_products_info_description ,
          'submit_language_products_new_title' => $submit_language_products_new_title ,
          'submit_language_products_new_keywords' => $submit_language_products_new_keywords ,
          'submit_language_products_new_description' => $submit_language_products_new_description ,
          'submit_language_special_title' => $submit_language_special_title,
          'submit_language_special_keywords' => $submit_language_special_keywords ,
          'submit_language_special_description' => $submit_language_special_description ,
          'submit_language_reviews_title' => $submit_language_reviews_title ,
          'submit_language_reviews_keywords' => $submit_language_reviews_keywords ,
          'submit_language_reviews_description' => $submit_language_reviews_description ,
          'submit_language_favorites_title' => $submit_language_favorites_title ,
          'submit_language_favorites_keywords' => $submit_language_favorites_keywords ,
          'submit_language_favorites_description' => $submit_language_favorites_description ,
          'submit_language_featured_title' => $submit_language_featured_title ,
          'submit_language_featured_keywords' => $submit_language_featured_keywords ,
          'submit_language_featured_description' => $submit_language_featured_description
        ];

        $update_sql = [
          'submit_id' => 1,
          'language_id' => $languages_id
        ];

          $CLICSHOPPING_SEO->db->save('submit_description', $sql_data_array_pages_description, $update_sql);

          $CLICSHOPPING_Hooks->call('SEO', 'Update');
        }

      $CLICSHOPPING_SEO->redirect('SEO&SEO');
    }
  }