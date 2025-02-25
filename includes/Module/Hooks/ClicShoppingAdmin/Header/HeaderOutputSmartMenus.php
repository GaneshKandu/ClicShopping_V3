<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\OM\Module\Hooks\ClicShoppingAdmin\Header;

  use ClicShopping\OM\CLICSHOPPING;

  class HeaderOutputSmartMenus
  {
    /**
     * @return bool|string
     */
    public function display(): string|bool
    {
      $output = '';

      if (isset($_SESSION['admin']) && VERTICAL_MENU_CONFIGURATION == 'false') {
        $output = '<!-- Start SmatMenus -->' . "\n";
        $output .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.smartmenus/1.2.1/css/sm-core-css.css" media="screen, print">' . "\n";
        $output .= '<link rel="stylesheet" href="' . CLICSHOPPING::link('css/smartmenus.min.css') . '" media="screen, print">' . "\n";
        $output .= '<link rel="stylesheet" href="' . CLICSHOPPING::link('css/smartmenus_customize.css') . '" media="screen, print">' . "\n";
        $output .= '<link rel="stylesheet" href="' . CLICSHOPPING::link('css/smartmenus_customize_responsive.css') . '" media="screen, print">' . "\n";
        $output .= '<!-- Start SmatMenus -->' . "\n";
      } else {
        return false;
      }

      return $output;
    }
  }