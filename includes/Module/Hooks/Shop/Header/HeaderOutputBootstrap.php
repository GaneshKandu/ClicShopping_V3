<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\OM\Module\Hooks\Shop\Header;

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\HTTP;
  
  class HeaderOutputBootstrap
  {
    /**
     * @return bool|string
     */
    public function display()
    {
      $CLICSHOPPING_Template = Registry::get('Template');

//Note : Could be relation with a meta tag allowing to implement a new boostrap theme : Must be installed
      if (!\defined('MODULE_HEADER_TAGS_BOOTSTRAP_SELECT_THEME') || MODULE_HEADER_TAGS_BOOTSTRAP_SELECT_THEME == 'False') {
        $output = '<!-- CSS only -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
' . "\n";
        $output .= '<link rel="stylesheet" media="screen, print" href="' . $CLICSHOPPING_Template->getTemplateCSS() . '" />' . "\n";
        $output .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">';

        return $output;
      } else {
        return false;
      }
    }
  }