<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Communication\Newsletter\Module\ClicShoppingAdmin\Config\NL;

  class NL extends \ClicShopping\Apps\Communication\Newsletter\Module\ClicShoppingAdmin\Config\ConfigAbstract
  {

    protected $pm_code = 'newsletter';

    public bool $is_uninstallable = true;
    public ?int $sort_order = 400;

    protected function init()
    {
      $this->title = $this->app->getDef('module_nl_title');
      $this->short_title = $this->app->getDef('module_nl_short_title');
      $this->introduction = $this->app->getDef('module_nl_introduction');
      $this->is_installed = \defined('CLICSHOPPING_APP_NEWSLETTER_NL_STATUS') && (trim(CLICSHOPPING_APP_NEWSLETTER_NL_STATUS) != '');
    }

    public function install()
    {
      parent::install();

      if (\defined('MODULE_MODULES_NEWSLETTER_INSTALLED')) {
        $installed = explode(';', MODULE_MODULES_NEWSLETTER_INSTALLED);
      }

      $installed[] = $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code;

      $this->app->saveCfgParam('NL', implode(';', $installed));
    }

    public function uninstall()
    {
      parent::uninstall();

      $installed = explode(';', MODULE_MODULES_NEWSLETTER_INSTALLED);
      $installed_pos = array_search($this->app->vendor . '\\' . $this->app->code . '\\' . $this->code, $installed);

      if ($installed_pos !== false) {
        unset($installed[$installed_pos]);

        $this->app->saveCfgParam('MODULE_MODULES_NEWSLETTER_INSTALLED', implode(';', $installed));
      }
    }
  }