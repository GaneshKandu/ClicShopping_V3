<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\OM\Module\Hooks\ClicShoppingAdmin\Footer;

  class FooterOutputMustache
  {
    /**
     * @return string|bool
     */
    public function display(): string|bool
    {
      $output = '';

      if (isset($_SESSION['admin'])) {
        $output .= '<!-- Mustache Script start-->' . "\n";
        $output .= '<script defer src="https://cdnjs.cloudflare.com/ajax/libs/mustache.js/4.2.0/mustache.min.js"></script>' . "\n";

        $output .= '<!--Mustache end -->' . "\n";
      } else {
        return false;
      }

      return $output;
    }
  }