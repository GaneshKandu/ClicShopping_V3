<?php
/**
 *
 *  @copyright 2008 - https://www.clicshopping.org
 *  @Brand : ClicShopping(Tm) at Inpi all right Reserved
 *  @Licence GPL 2 & MIT
 *  @Info : https://www.clicshopping.org/forum/trademark/
 *
 */

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\CLICSHOPPING;

  class he_header_noscript {
    public string $code;
    public string $group;
    public $title;
    public $description;
    public ?int $sort_order = 0;
    public bool $enabled = false;

    public function __construct() {
      $this->code = get_class($this);
      $this->group = basename(__DIR__);
      $this->title = CLICSHOPPING::getDef('module_header_noscript_title');
      $this->description = CLICSHOPPING::getDef('module_header_noscript_description');

      if (\defined('MODULE_HEADER_NOSCRIPT_STATUS')) {
        $this->sort_order = (int)MODULE_HEADER_NOSCRIPT_SORT_ORDER ?? 0;
        $this->enabled = (MODULE_HEADER_NOSCRIPT_STATUS == 'True');
      }
    }

    public function execute() {
      $CLICSHOPPING_Template = Registry::get('Template');

      $content_width = MODULE_HEADER_NOSCRIPT_CONTENT_WIDTH;

      $header_template = '<!-- header no script start -->';

      ob_start();
      require_once($CLICSHOPPING_Template->getTemplateModules($this->group . '/content/header_noscript'));
      $header_template .= ob_get_clean();

      $header_template .= '<!-- header no script end -->' . "\n";

      $CLICSHOPPING_Template->addBlock($header_template, $this->group);
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return \defined('MODULE_HEADER_NOSCRIPT_STATUS');
    }

    public function install() {
      $CLICSHOPPING_Db = Registry::get('Db');


      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Do you want to activate this module?',
          'configuration_key' => 'MODULE_HEADER_NOSCRIPT_STATUS',
          'configuration_value' => 'True',
          'configuration_description' => 'Do you want to activate this module?',
          'configuration_group_id' => '6',
          'sort_order' => '1',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Please indicate the width of the content',
          'configuration_key' => 'MODULE_HEADER_NOSCRIPT_CONTENT_WIDTH',
          'configuration_value' => '12',
          'configuration_description' => 'Please specify a display width',
          'configuration_group_id' => '6',
          'sort_order' => '2',
          'set_function' => 'clic_cfg_set_content_module_width_pull_down',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Sort order',
          'configuration_key' => 'MODULE_HEADER_NOSCRIPT_SORT_ORDER',
          'configuration_value' => '1',
          'configuration_description' => 'Sort order of display. Lowest is displayed first. The sort order must be different on every module',
          'configuration_group_id' => '6',
          'sort_order' => '0',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );
    }

    public function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    public function keys() {
      return array('MODULE_HEADER_NOSCRIPT_STATUS',
                   'MODULE_HEADER_NOSCRIPT_CONTENT_WIDTH',
                   'MODULE_HEADER_NOSCRIPT_SORT_ORDER'
                  );
    }
  }
