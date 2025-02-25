<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT

   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

namespace ClicShopping\Apps\Customers\Gdpr\Module\Hooks\ClicShoppingAdmin\TopDashboard;

  use ClicShopping\OM\CLICSHOPPING;
  use ClicShopping\OM\Registry;
  use ClicShopping\OM\HTML;

  use ClicShopping\Apps\Customers\Gdpr\Gdpr as GdprApp;

  class Gdpr implements \ClicShopping\OM\Modules\HooksInterface
  {
    /**
     * @var bool|null
     */
    protected mixed $app;

    public function __construct()
    {
      if (!Registry::exists('Gdpr')) {
        Registry::set('Gdpr', new GdprApp());
      }

      $this->app = Registry::get('Gdpr');

      $this->app->loadDefinitions('Module/Hooks/ClicShoppingAdmin/TopDashboard/gdpr');
    }

    public function Display(): string
    {
      if (!\defined('CLICSHOPPING_APP_CUSTOMERS_GDPR_GD_STATUS') || CLICSHOPPING_APP_CUSTOMERS_GDPR_GD_STATUS == 'False') {
        return false;
      }

      $date = date('Y-m-d', strtotime('+ ' . CLICSHOPPING_APP_CUSTOMERS_GDPR_GD_DATE . ' days'));

      $QstatGdpr = $this->app->db->prepare('select c.customers_id,
                                            datediff(now(), ci.customers_info_date_of_last_logon) as datediff
                                            from :table_customers c,
                                                 :table_customers_info ci
                                            where c.gdpr = 0
                                            and c.customers_id = ci.customers_info_id
                                           ');

      $QstatGdpr->execute();

      $count = 0;

      while($QstatGdpr->fetch()) {
        if ($QstatGdpr->value('datediff') > (int)CLICSHOPPING_APP_CUSTOMERS_GDPR_GD_DATE) {
          ++$count;
        }
      }

      $text_gdpr = $this->app->getDef('text_number_gdpr');
      $text_view = $this->app->getDef('text_view');
      $output = '';

      if ($count > 0) {
        $output = '
<div class="col-md-2 col-12 m-1">
    <div class="card bg-warning">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12">
            <div class="row">
            <span class="col-sm-10"><h6 class="card-title text-white"><i class="bi bi-headset"  style="font-size: 1.3rem;"></i> ' . $text_gdpr . '<br></h6></span>         
            </div>
          </div> 
        </div>
        <div class="col-md-12">
          <span class="text-white"><strong>' . $count . '</strong></span>
          <span><small class="text-white">' . HTML::link(CLICSHOPPING::link(null, 'A&Customers\Gdpr&Gdpr'), $text_view, 'class="text-white"') . '</small></span>
        </div>
      </div>
    </div>
</div>
';
      }

      return $output;
    }
  }