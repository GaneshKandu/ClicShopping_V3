<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Configuration\Api\Sites\ClicShoppingAdmin\Pages\Home\Actions\Api;

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\Cache;

  use ClicShopping\Apps\Configuration\Api\Classes\ClicShoppingAdmin\ApiAdmin;

  class UpdateAll extends \ClicShopping\OM\PagesActionsAbstract
  {
    protected mixed $app;
    protected mixed $db;

    public function __construct()
    {
      $this->app = Registry::get('Api');
    }

    public function execute()
    {
      $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;

      if (!Registry::exists('ApiAdmin')) {
        Registry::set('ApiAdmin', new ApiAdmin());
      }

      $ApiAdmin = Registry::get('ApiAdmin');

      $ApiAdmin->updateAllApi();

      Cache::clear('api');

      $this->app->redirect('Api&page=' . $page);
    }
  }