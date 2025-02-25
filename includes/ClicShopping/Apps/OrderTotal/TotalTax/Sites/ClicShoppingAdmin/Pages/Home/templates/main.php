<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  use ClicShopping\OM\HTML;
  use ClicShopping\OM\CLICSHOPPING;
  use ClicShopping\OM\Registry;

  $CLICSHOPPING_MessageStack = Registry::get('MessageStack');
  $CLICSHOPPING_TotalTax = Registry::get('TotalTax');
  $CLICSHOPPING_Template = Registry::get('TemplateAdmin');

  if ($CLICSHOPPING_MessageStack->exists('TotalTax')) {
    echo $CLICSHOPPING_MessageStack->get('TotalTax');
  }
?>
<div class="contentBody">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-block headerCard">
        <div class="row">
          <span
            class="col-md-1 logoHeading"><?php echo HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'categories/modules_modules_checkout_payment.gif', $CLICSHOPPING_TotalTax->getDef('heading_title'), '40', '40'); ?></span>
          <span
            class="col-md-4 pageHeading"><?php echo '&nbsp;' . $CLICSHOPPING_TotalTax->getDef('heading_title'); ?></span>
        </div>
      </div>
    </div>
  </div>
  <div class="separator"></div>
  <div class="col-md-12 mainTitle"><strong><?php echo $CLICSHOPPING_TotalTax->getDef('text_tax'); ?></strong></div>
  <div class="adminformTitle">
    <div class="row">
      <div class="separator"></div>

      <div class="col-md-12">
        <div>
          <div class="col-md-12">
            <?php echo $CLICSHOPPING_TotalTax->getDef('text_intro'); ?>
          </div>
        </div>
        <div class="separator"></div>

        <div class="col-md-12 text-center">
          <div>
            <div class="col-md-12">
              <?php
                echo HTML::form('configure', CLICSHOPPING::link(null, 'A&OrderTotal\TotalTax&Configure'));
                echo HTML::button($CLICSHOPPING_TotalTax->getDef('button_configure'), null, null, 'primary');
                echo '</form>';
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
