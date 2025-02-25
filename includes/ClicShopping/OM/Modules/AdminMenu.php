<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */


  namespace ClicShopping\OM\Modules;

  use ClicShopping\OM\Apps;

  class AdminMenu extends \ClicShopping\OM\ModulesAbstract
  {
    public function getInfo($app, $key, $data)
    {
      $result = [];

      $class = $this->ns . $app . '\\' . $data;

      if (is_subclass_of($class, 'ClicShopping\OM\Modules\\' . $this->code . 'Interface')) {
        $result[$app . '\\' . $key] = $class;
      }

      return $result;
    }

    public function getClass($module)
    {

      [$vendor, $app, $code] = explode('\\', $module, 3);

      $info = Apps::getInfo($vendor . '\\' . $app);

      if (isset($info['modules'][$this->code][$code])) {
        return $this->ns . $vendor . '\\' . $app . '\\' . $info['modules'][$this->code][$code];
      } else {
        return false;
      }
    }
  }
