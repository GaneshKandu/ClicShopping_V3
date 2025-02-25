<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Configuration\Weight\Sites\ClicShoppingAdmin\Pages\Home\Actions\Weight;

  use ClicShopping\OM\HTML;
  use ClicShopping\OM\Registry;
  use ClicShopping\OM\Cache;

  class ClassUpdate extends \ClicShopping\OM\PagesActionsAbstract
  {
    protected mixed $app;

    public function __construct()
    {
      $this->app = Registry::get('Weight');
    }

    public function execute()
    {
      $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
      $weight_class_from_id_old = HTML::sanitize($_GET['wID']);
      $weight_class_to_id_old = HTML::sanitize($_GET['tID']);

      $weight_class_from_id = HTML::sanitize($_POST['weight_class_id']);
      $weight_class_to_id = HTML::sanitize($_POST['weight_class_to_id']);
      $weight_class_rule = HTML::sanitize($_POST['weight_class_rule']);


      $Qcheck = $this->app->db->prepare('select weight_class_from_id,
                                                weight_class_to_id
                                          from :table_weight_classes_rules
                                          where weight_class_from_id = :weight_class_from_id_old
                                          and weight_class_to_id = :weight_class_to_id_old
                                        ');

      $Qcheck->bindInt(':weight_class_from_id_old', $weight_class_from_id_old);
      $Qcheck->bindInt(':weight_class_to_id_old', $weight_class_to_id_old);
      $Qcheck->execute();


      if ($Qcheck->fetch()) {
        $Qupdate = $this->app->db->prepare('update :table_weight_classes_rules
                                            set weight_class_from_id = :weight_class_from_id,
                                            weight_class_to_id = :weight_class_to_id,
                                            weight_class_rule = :weight_class_rule
                                            where weight_class_from_id = :weight_class_from_id_old
                                            and weight_class_to_id = :weight_class_to_id_old
                                          ');

        $Qupdate->bindInt(':weight_class_from_id', $weight_class_from_id);
        $Qupdate->bindInt(':weight_class_to_id', $weight_class_to_id);
        $Qupdate->bindDecimal(':weight_class_rule', $weight_class_rule);
        $Qupdate->bindInt(':weight_class_from_id_old', $weight_class_from_id_old);
        $Qupdate->bindInt(':weight_class_to_id_old', $weight_class_to_id_old);
        $Qupdate->execute();
      }

      Cache::clear('weight-classes');
      Cache::clear('weight-rules');

      $this->app->redirect('Weight&page=' . $page);
    }
  }