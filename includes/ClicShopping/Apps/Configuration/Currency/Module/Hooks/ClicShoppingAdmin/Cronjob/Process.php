<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Configuration\Currency\Module\Hooks\ClicShoppingAdmin\Cronjob;

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\HTML;
  use ClicShopping\OM\Cache;

  use ClicShopping\Apps\Customers\Gdpr\Gdpr as GdprApp;

  use ClicShopping\Apps\Tools\Cronjob\Classes\ClicShoppingAdmin\Cron;
  use ClicShopping\Apps\Configuration\Currency\Classes\ClicShoppingAdmin\CurrenciesAdmin;

  class Process implements \ClicShopping\OM\Modules\HooksInterface
  {
    public function __construct()
    {
    }

    /**
     * @return array
     */
    private static function updateAllCurrencies() :void
    {
      if (!Registry::exists('CurrenciesAdmin')) {
        Registry::set('CurrenciesAdmin', new CurrenciesAdmin());
      }

      $CurrenciesAdmin = Registry::get('CurrenciesAdmin');

      $CurrenciesAdmin->updateAllCurrencies();
    }

    /**
     *
     */
    private static function cronJob() :void
    {
      $cron_id_gdpr = Cron::getCronCode('currency');

      if (isset($_GET['cronId'])) {
        $cron_id = HTML::sanitize($_GET['cronId']);

        Cron::updateCron($cron_id);

        if (isset($cron_id) && $cron_id_gdpr == $cron_id) {
          static::updateAllCurrencies();
        }
      } else {
        Cron::updateCron($cron_id_gdpr);

        if (isset($cron_id_gdpr)) {
          static::updateAllCurrencies();
        }
      }
    }

    public function execute()
    {
      static::cronJob();

      Cache::clear('currencies');
    }
  }