<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\OM;

  use ClicShopping\OM\CLICSHOPPING;
  use ClicShopping\OM\Registry;

  abstract class AppAbstract
  {
    public string $code;
    public $title;
    public string $vendor;
    public string $version;
    public array $modules = [];

    public mixed $db;
    public mixed $lang;

    abstract protected function init();

    final public function __construct()
    {
      $this->setInfo();

      $this->db = Registry::get('Db');
      $this->lang = Registry::get('Language');
      $this->init();
    }

    /**
     * @return string
     */
    final public function link() :string
    {
      $args = \func_get_args();

      $parameters = 'A&' . $this->vendor . '\\' . $this->code;

      if (isset($args[0])) {
        $args[0] = $parameters .= '&' . $args[0];
      } else {
        $args[0] = $parameters;
      }

      array_unshift($args, 'index.php');

      return forward_static_call_array([
        'ClicShopping\OM\CLICSHOPPING',
        'link'
      ], $args);
    }

    /**
     * @return string
     */
    final public function redirect() :string
    {
      $args = \func_get_args();

      $parameters = 'A&' . $this->vendor . '\\' . $this->code;

      if (isset($args[0])) {
        $args[0] = $parameters .= '&' . $args[0];
      } else {
        $args[0] = $parameters;
      }

      array_unshift($args, 'index.php');

      return forward_static_call_array([
        'ClicShopping\OM\CLICSHOPPING',
        'redirect'
      ], $args);
    }

    /**
     * @return string
     */
    final public function getCode() :string
    {
      return $this->code;
    }

    /**
     * @return mixed
     */
    final public function getVendor() :string
    {
      return $this->vendor;
    }

    /**
     * @return string
     */
    final public function getTitle() :string
    {
      return $this->title;
    }

    /**
     * @return string
     */
    final public function getVersion() :string
    {
      return $this->version;
    }

    /**
     * @return string
     */
    final public function getModules() :string
    {
      return $this->modules;
    }

    /**
     * @param string $module
     * @param string $type
     */
    final public function hasModule(string $module, string $type)
    {
    }

    /**
     * @return bool
     * @throws \ReflectionException
     */
    private function setInfo()
    {
      $r = new \ReflectionClass($this);

      $this->code = $r->getShortName();
      $this->vendor = \array_slice(explode('\\', $r->getNamespaceName()), -2, 1)[0];

      $metafile = CLICSHOPPING::BASE_DIR . 'Apps/' . $this->vendor . '/' . $this->code . '/clicshopping.json';

      if (!is_file($metafile) || (($json = json_decode(file_get_contents($metafile), true)) === null)) {
        trigger_error('ClicShopping\OM\AppAbstract::setInfo(): ' . $this->vendor . '\\' . $this->code . ' - Could not read App information in ' . $metafile . '.');

        return false;
      }

      $this->title = $json['title'];
      $this->version = $json['version'];

      if (!empty($json['modules'])) {
        $this->modules = $json['modules'];
      }
    }

    /**
     * @return string
     */
    final public function getDef() :string
    {
      $args = \func_get_args();

      if (!isset($args[0])) {
        $args[0] = null;
      }

      if (!isset($args[1])) {
        $args[1] = null;
      }

      if (!isset($args[2])) {
        $args[2] = $this->vendor . '-' . $this->code;
      }

      return \call_user_func_array([$this->lang, 'getDef'], $args);
    }

    /**
     * @param $group
     * @param null $language_code
     * @return bool|mixed
     */
    final public function definitionsExist(string $group, ?string $language_code = null)
    {
      $language_code = isset($language_code) && $this->lang->exists($language_code) ? $language_code : $this->lang->get('code');

      $pathname = CLICSHOPPING::BASE_DIR . 'Apps/' . $this->vendor . '/' . $this->code . '/languages/' . $this->lang->get('directory', $language_code) . '/' . $group . '.txt';

      if (is_file($pathname)) {
        return true;
      }

      if ($language_code != DEFAULT_LANGUAGE) {
        return \call_user_func([$this, __FUNCTION__], $group, DEFAULT_LANGUAGE);
      }

      return false;
    }

    /**
     * @param string $group
     * @param string|null $language_code
     */
    final public function loadDefinitions(string $group, ?string $language_code = null) :void
    {
      $language_code = isset($language_code) && $this->lang->exists($language_code) ? $language_code : $this->lang->get('code');

      if ($language_code != DEFAULT_LANGUAGE) {
        $this->loadDefinitions($group, DEFAULT_LANGUAGE);
      }

      $pathname = CLICSHOPPING::BASE_DIR . 'Apps/' . $this->vendor . '/' . $this->code . '/languages/' . $this->lang->get('directory', $language_code) . '/' . $group . '.txt';

      if (!is_file($pathname)) {
        $language_code = DEFAULT_LANGUAGE;
        $pathname = CLICSHOPPING::BASE_DIR . 'Apps/' . $this->vendor . '/' . $this->code . '/languages/' . $this->lang->get('directory', $language_code) . '/' . $group . '.txt';
      }

      $group = 'Apps/' . $this->vendor . '/' . $this->code . '/' . $group;

      $defs = $this->lang->getDefinitions($group, $language_code, $pathname);

      $this->lang->injectDefinitions($defs, $this->vendor . '-' . $this->code);
    }

    /**
     * @param string $key
     * @param string $value
     * @param string|null $title
     * @param string|null $description
     * @param string|null $set_func
     */
    final public function saveCfgParam($key, $value, $title = null, $description = null, $set_func = null) :void
    {
      if (\is_null($value)) {
        $value = '';
      }

      if (!\defined($key)) {
        if (!isset($title)) {
          $title = 'Parameter [' . $this->getTitle() . ']';
        }

        if (!isset($description)) {
          $description = 'Parameter [' . $this->getTitle() . ']';
        }

        $data = [
          'configuration_title' => $title,
          'configuration_key' => $key,
          'configuration_value' => $value,
          'configuration_description' => $description,
          'configuration_group_id' => '6',
          'sort_order' => '0',
          'date_added' => 'now()'
        ];

        if (isset($set_func)) {
          $data['set_function'] = $set_func;
        }

        $this->db->save('configuration', $data);

        define($key, $value);
      } else {
        $this->db->save('configuration', [
          'configuration_value' => $value
        ], [
          'configuration_key' => $key
        ]);
      }
    }

    /**
     * @param string $key
     */
    final public function deleteCfgParam(string $key) :void
    {
      $this->db->delete('configuration', [
        'configuration_key' => $key
      ]);
    }

    /**
     * to remove not update with some tab
     * @param array $result
     * @param string $directory
     * @param string $name_space_config
     */

    final public function getConfigApps (array $result, string $directory, string $name_space_config, string $trigger_message) :void
    {
      if ($dir = new \DirectoryIterator($directory)) {
        foreach ($dir as $file) {
          if (!$file->isDot() && $file->isDir() && is_file($file->getPathname() . '/' . $file->getFilename() . '.php')) {
            $class = '' . $name_space_config . '\\' . $file->getFilename() . '\\' . $file->getFilename();

            if (is_subclass_of($class, '' . $name_space_config .'\ConfigAbstract')) {
              $sort_order = $this->getConfigModuleInfo($file->getFilename(), 'sort_order');
              if ($sort_order > 0) {
                $counter = $sort_order;
              } else {
                $counter = count($result);
              }

              while (true) {
                if (isset($result[$counter])) {
                  $counter++;

                  continue;
                }

                $result[$counter] = $file->getFilename();

                break;
              }
            } else {
              trigger_error('' . $trigger_message .'' . $name_space_config .'\\' . $file->getFilename() . '\\' . $file->getFilename() . ' is not a subclass of ' . $name_space_config . '\ConfigAbstract and cannot be loaded.');
            }
          }
        }

        ksort($result, SORT_NUMERIC);
      }
    }
  }
