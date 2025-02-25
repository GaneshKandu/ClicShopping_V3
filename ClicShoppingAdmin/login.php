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
  use ClicShopping\OM\Is;
  use ClicShopping\OM\Hash;
  use ClicShopping\OM\HTTP;

  use ClicShopping\Apps\Configuration\TemplateEmail\Classes\ClicShoppingAdmin\TemplateEmailAdmin;
  use ClicShopping\Sites\ClicShoppingAdmin\ActionRecorderAdmin;
  use ClicShopping\Sites\Common\Topt;

  $login_request = true;

  require_once __DIR__ . '/includes/application_top.php';

  $CLICSHOPPING_Db = Registry::get('Db');
  $CLICSHOPPING_MessageStack = Registry::get('MessageStack');
  $CLICSHOPPING_Mail = Registry::get('Mail');
  $CLICSHOPPING_Hooks = Registry::get('Hooks');
  $CLICSHOPPING_Template = Registry::get('TemplateAdmin');

  $action = $_GET['action'] ?? '';

// prepare to logout an active administrator if the login page is accessed again
  if (isset($_SESSION['admin'])) {
    $action = 'logoff';
  }

  if (!\is_null($action)) {
    switch ($action) {
      case 'loginAuth':
        $error = false;

        if (isset($_POST['username'], $_POST['password'])) {
          $_SESSION['username'] = HTML::sanitize($_POST['username']);
          $_SESSION['password'] = HTML::sanitize($_POST['password']);

          $username = $_SESSION['username'];
          $password = $_SESSION['password'];
        } else {
          $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('error_invalid_administrator'), 'error');

          $CLICSHOPPING_Hooks->call('Login', 'ErrorProcess');
        }

        Registry::set('ActionRecorderAdmin', new ActionRecorderAdmin('ar_admin_login', null, $username));
        $CLICSHOPPING_ActionRecorder = Registry::get('ActionRecorderAdmin');

        if ($CLICSHOPPING_ActionRecorder->canPerform()) {
          $sql_array = [
            'id',
            'user_name',
            'user_password',
            'name',
            'first_name',
            'access',
            'double_authentification_secret'
          ];

          $Qcheck = $CLICSHOPPING_Db->get('administrators', $sql_array, ['user_name' => $username]);

          if ($Qcheck->fetch()) {
            if (Hash::verify($password, $Qcheck->value('user_password'))) {

              $sql_array = [
                'id',
                'username',
                'access',
                'double_authentification_secret'
              ];

              $Qcheck = $CLICSHOPPING_Db->get('administrators', 'double_authentification_secret', ['user_name' => $username]);

              $_SESSION['adminAuth'] = [
                'id' => $Qcheck->valueInt('id'),
                'username' => $Qcheck->value('user_name'),
                'access' => $Qcheck->value('access')
              ];

              $CLICSHOPPING_ActionRecorder->_user_id = $_SESSION['adminAuth']['id'];
              $CLICSHOPPING_ActionRecorder->record();

              if (empty(Topt::checkAuthAdmin($username))) {
                $_SESSION['tfa_secret'] = Topt::getTfaSecret();
                $update_array = ['double_authentification_secret' => $_SESSION['tfa_secret']];

                $CLICSHOPPING_Db->save('administrators', $update_array, ['user_name' => $username]);
              } else if (empty($_SESSION['tfa_secret'])) {
                $_SESSION['tfa_secret'] = $Qcheck->value('double_authentification_secret');
              }
            }
          } else {
            $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('error_invalid_administrator'), 'error');

            $CLICSHOPPING_Hooks->call('Login', 'ErrorProcess');
          }
        } else {
          $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('error_action_recorder', ['module_action_recorder_admin_login_minutes' => (\defined('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES') ? (int)MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES : 5)]));
        }

        if (isset($_POST['username'])) {
          $CLICSHOPPING_ActionRecorder->record(false);
        }
      break;

      case 'loginAuthProcess':
        $error = true;
// Check the topt
        if (isset($_POST['tfa_code'])) {
          $tfaCode = HTML::sanitize($_POST['tfa_code']);

          if (empty($tfaCode)) {
            CLICSHOPPING::redirect('login.php?action=loginAuth');
          } else {
            if (!empty(Topt::checkAuthAdmin($_SESSION['username']))) {
              if (Topt::getVerifyAuth($_SESSION['tfa_secret'], $tfaCode) === true) {
                $username = HTML::sanitize($_SESSION['username']);
                $password = HTML::sanitize($_SESSION['password']);

                if (!empty($username) && !empty($password)) {
                    $sql_array = [
                      'user_name',
                      'user_password',
                    ];

                    $Qadmin = $CLICSHOPPING_Db->get('administrators', $sql_array, ['user_name' => $username]);

                    if ($Qadmin->fetch() !== false) {
                      if (Hash::verify($password, $Qadmin->value('user_password'))) {
                        $_SESSION['admin'] = [
                          'id' => $Qadmin->valueInt('id'),
                          'username' => $Qadmin->value('user_name'),
                          'access' => $Qadmin->value('access')
                        ];

                        if (isset($_SESSION['redirect_origin'])) {
                          $page = $_SESSION['redirect_origin']['page'];

                          $get_string = http_build_query($_SESSION['redirect_origin']['get']);

                          unset($_SESSION['redirect_origin']);
                          Topt::resetAllAdmin();

                          $CLICSHOPPING_Hooks->call('Login', 'Process');

                          CLICSHOPPING::redirect($page, $get_string);
                        } else {
                          CLICSHOPPING::redirect();
                        }
                      }
                    }

                    if (isset($_POST['username'])) {
                      $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('error_invalid_administrator'), 'error');

                      $CLICSHOPPING_Hooks->call('Login', 'ErrorProcess');
                    }
                } else {
                  unset($_SESSION['user_secret']);
                  CLICSHOPPING::redirect('login.php');
                }
              } else {
                $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('text_code_auth_invalid'), 'error');
                unset($_SESSION['user_secret']);
                CLICSHOPPING::redirect('login.php?action=loginAuth');
              }
            } else {
              $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('text_code_auth_invalid'), 'error');
              unset($_SESSION['user_secret']);
              CLICSHOPPING::redirect('login.php?action=loginAuth');
            }
          }
        } else {
          $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('text_code_auth_invalid'), 'error');
          unset($_SESSION['user_secret']);
          CLICSHOPPING::redirect('login.php?action=loginAuth');
        }
      break;

      case 'process':
        $CLICSHOPPING_Hooks->call('PreAction', 'Process');
        $username = '';
        $password = '';

        if (isset($_SESSION['redirect_origin'], $_SESSION['redirect_origin']['auth_user']) && !isset($_POST['username'])) {
          $username = HTML::sanitize($_SESSION['redirect_origin']['auth_user']);
          $password = HTML::sanitize($_SESSION['redirect_origin']['auth_pw']);
        } else {
          if (isset($_POST['username'], $_POST['password'])) {
            $username = HTML::sanitize($_POST['username']);
            $password = HTML::sanitize($_POST['password']);
          } else {
            CLICSHOPPING::redirect('login.php');
          }
        }

        if (!empty($username)) {
          Registry::set('ActionRecorderAdmin', new ActionRecorderAdmin('ar_admin_login', null, $username));
          $CLICSHOPPING_ActionRecorder = Registry::get('ActionRecorderAdmin');

          if ($CLICSHOPPING_ActionRecorder->canPerform()) {
            $sql_array = [
              'id',
              'user_name',
              'user_password',
              'name',
              'first_name',
              'access'
            ];

            $Qadmin = $CLICSHOPPING_Db->get('administrators', $sql_array, ['user_name' => $username]);

            if ($Qadmin->fetch() !== false) {
              if (Hash::verify($password, $Qadmin->value('user_password'))) {
                $_SESSION['admin'] = [
                  'id' => $Qadmin->valueInt('id'),
                  'username' => $Qadmin->value('user_name'),
                  'access' => $Qadmin->value('access')
                ];

                $CLICSHOPPING_ActionRecorder->_user_id = $_SESSION['admin']['id'];
                $CLICSHOPPING_ActionRecorder->record();

                if (isset($_SESSION['redirect_origin'])) {
                  $page = $_SESSION['redirect_origin']['page'];

                  $get_string = http_build_query($_SESSION['redirect_origin']['get']);

                  unset($_SESSION['redirect_origin']);

                  $CLICSHOPPING_Hooks->call('Login', 'Process');

                  CLICSHOPPING::redirect($page, $get_string);
                } else {
                  CLICSHOPPING::redirect();
                }
              }
            }

            if (isset($_POST['username'])) {
              $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('error_invalid_administrator'), 'error');

              $CLICSHOPPING_Hooks->call('Login', 'ErrorProcess');
            }
          } else {
            $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('error_action_recorder', ['module_action_recorder_admin_login_minutes' => (\defined('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES') ? (int)MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES : 5)]));
          }

          if (isset($_POST['username'])) {
            $CLICSHOPPING_ActionRecorder->record(false);
          }
        }
      break;

      case 'logoff':
        $CLICSHOPPING_Hooks->call('Account', 'LogoutBefore');

        unset($_SESSION['admin']);
        Topt::resetAllAdmin();

        if (isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && !empty($_SERVER['PHP_AUTH_PW'])) {
          $_SESSION['auth_ignore'] = true;
        }

        $CLICSHOPPING_Hooks->call('Account', 'LogoutAfter');

        CLICSHOPPING::redirect();
        break;

      case 'create':
        $CLICSHOPPING_Hooks->call('PreAction', 'Create');

        $Qcheck = $CLICSHOPPING_Db->get('administrators', 'id', null, null, 1);

        if (!$Qcheck->check()) {
          $username = HTML::sanitize($_POST['username']);
          $password = HTML::sanitize($_POST['password']);
          $name = HTML::sanitize($_POST['name']);
          $first_name = HTML::sanitize($_POST['first_name']);

          if (!empty($username)) {
            $insert_array = [
              'user_name' => $username,
              'user_password' => Hash::encrypt($password),
              'name' => $name,
              'first_name' => $first_name,
              'access' => 1
            ];

            $CLICSHOPPING_Db->save('administrators', $insert_array);
          }
        }

        $CLICSHOPPING_Hooks->call('Login', 'Create');

        CLICSHOPPING::redirect('login.php');

      break;

      case 'send_password':
        $error = false;

        $CLICSHOPPING_Hooks->call('PreAction', 'SendPassword');

        if ($error === false) {
          $username = HTML::sanitize($_POST['username']);

          $Qcheck = $CLICSHOPPING_Db->prepare('select id
                                               from :table_administrators
                                               where user_name = :user_name
                                               limit 1
                                              ');
          $Qcheck->bindValue(':user_name', $username);
          $Qcheck->execute();

          if ($Qcheck->rowCount() === 1 && Is::EmailAddress($username)) {
            $new_password = Hash::getRandomString((int)ENTRY_PASSWORD_MIN_LENGTH);
            $crypted_password = Hash::encrypt($new_password);

            $Qupdate = $CLICSHOPPING_Db->prepare('update :table_administrators
                                                   set user_password = :user_password
                                                   where user_name = :user_name
                                                   limit 1
                                                ');
            $Qupdate->bindValue(':user_password', $crypted_password);
            $Qupdate->bindValue(':user_name', $username);

            $Qupdate->execute();

            $body_subject = CLICSHOPPING::getDef('email_password_reminder_subject', ['store_name' => STORE_NAME]);

            $text_array =  [
              'store_name' => STORE_NAME,
              'remote_address' => $_SERVER['REMOTE_ADDR'],
              'new_password' => $new_password
            ];

            $email_body = CLICSHOPPING::getDef('email_password_reminder_body', $text_array) . "\n";
            $email_body .= TemplateEmailAdmin::getTemplateEmailSignature() . "\n";
            $email_body .= TemplateEmailAdmin::getTemplateEmailTextFooter();

            $to_addr = $username;
            $from_name = STORE_OWNER;
            $from_addr = STORE_OWNER_EMAIL_ADDRESS;
            $to_name = NULL;
            $subject = $body_subject;

            $CLICSHOPPING_Mail->addHtml($email_body);
            $CLICSHOPPING_Mail->send($to_addr, $from_name, $from_addr, $to_name, $subject);

            $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('success_password_sent'), 'success');
          } else {
            $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('text_no_email_address_found'), 'error, again 1 time before to block your IP address');
          }

          $CLICSHOPPING_Hooks->call('Login', 'SendPassword');

          CLICSHOPPING::redirect('login.php');
        }

        break;
    }
  }

  $Qcheck = $CLICSHOPPING_Db->get('administrators', 'id', null, null, 1);

  if (!$Qcheck->check()) {
    $CLICSHOPPING_MessageStack->add(CLICSHOPPING::getDef('text_create_first_administrator'), 'warning');
  }

  require_once($CLICSHOPPING_Template->getTemplateHeaderFooterAdmin('header.php'));

  require_once ('background.php');

  $ip = HTTP::getIpAddress();

  if (Is::IpAddress($ip) && (!empty($ip) || !\is_null($ip))) {
    $details = file_get_contents("https://ipinfo.io/{$ip}/geo");

    if ($details !== false) {
      $details = json_decode($details);
      if(isset($details->country)) {
        $country = $details->country;
        echo "<script>$('svg path[data-country-code={$country}]').attr('fill', '#197ac6').attr('fill-opacity', '0.15');</script>";
      }
    }
  }
?>
  <div class="loader-wrapper"></div>
<?php
  if ($Qcheck->check()) {
    if (CLICSHOPPING_TOTP_ADMIN == 'True') {
      if (!empty($_SESSION['tfa_secret'])) {
        $form_action = 'loginAuthProcess';
      } else {
        $form_action = 'loginAuth';
      }
    } else {
      $form_action = 'process';
    }

    $button_text = CLICSHOPPING::getDef('button_login');
  } else {
    $form_action = 'create';
    $button_text = CLICSHOPPING::getDef('button_create_administrator');
  }

  if ($action != 'password') {
?>
    <div id="loginModal" tabindex="-1" role="document" aria-hidden="true" style="padding-top:10rem;">
      <div class="modal-dialog">
        <div class="modal-content" style="background-color: transparent; border: none; align-items: center;">
          <div class="modal-header">
            <h1 style="color:#233C7A; text-align: center;"><?php echo CLICSHOPPING::getDef('heading_title'); ?></h1>
          </div>
          <?php echo HTML::form('login', CLICSHOPPING::link('login.php', 'action=' . $form_action)); ?>
          <div class="modal-body" style="width:20rem; padding-top:3rem;">
            <div class="col-md-12 center-block">
<?php
    if ($form_action == 'create') {
?>
            <div class="input-group">
              <span class="input-group-addon" id="basic-addon1"></span>
              <?php echo HTML::inputField('first_name', '', 'placeholder="' . CLICSHOPPING::getDef('text_firstname') . '" required aria-required="true" autocomplete="off" aria-describedby="basic-addon1"'); ?>
            </div>
            <div class="separator"></div>
            <div class="input-group">
              <span class="input-group-addon" id="basic-addon1"></span>
              <?php echo HTML::inputField('name', '', 'placeholder="' . CLICSHOPPING::getDef('text_name') . '" required aria-required="true" autocomplete="off" aria-describedby="basic-addon1"'); ?>
            </div>
            <div class="separator"></div>
<?php
    } elseif(!empty($_SESSION['tfa_secret'])) {
?>
            <div class="contentText">
            <?php
               if (empty($_SESSION['user_secret'])) {
            ?>
              <div class="col-md-12 text-center">
                <?php echo Topt::getImageTopt(CLICSHOPPING_TOTP_SHORT_TILTE, $_SESSION['tfa_secret']); ?>
                <div class="separator"></div>
                <div class="row">
                  <span class="col-md-12"><?php echo HTML::inputField('tfa_code', null, 'aria-required="true" required placeholder="' . CLICSHOPPING::getDef('text_auth_code') . '"'); ?></span>
                </div>
                <div class="separator"></div>
                <span class="col-md-6">
                    <label for="buttonContinue"><?php echo HTML::button(CLICSHOPPING::getDef('button_continue'), null, null, 'success'); ?></label>
                    <label for="buttonCancel"><?php echo HTML::button(CLICSHOPPING::getDef('button_cancel'), null, 'login.php?action=logoff', 'warning'); ?></label>
                  </span>
              </div>
              <div class="separator"></div>
              <div class="col-md-12"><?php echo CLICSHOPPING::getDef('text_Login_auth_introduction'); ?></div>
            <?php
              }
            ?>
            </div>
<?php
    } elseif(empty($_SESSION['tfa_secret'])) {
?>
              <div class="input-group">
                <span class="input-group-addon" id="basic-addon1"></span>
                <?php echo HTML::inputField('username', '', 'placeholder="' . CLICSHOPPING::getDef('text_username') . '" required aria-required="true" autocomplete="off" aria-describedby="basic-addon1"'); ?>
              </div>
              <div class="separator"></div>
              <div class="input-group">
                <span class="input-group-addon" id="basic-addon1"></span>
                <?php echo HTML::passwordField('password', '', 'placeholder="' . CLICSHOPPING::getDef('text_password') . '" required aria-required="true" autocomplete="off" aria-describedby="basic-addon1"'); ?>
              </div>
              <div class="separator"></div>
              <div class="text-end">
                <label for="buttonText"><?php echo HTML::button($button_text, null, null, 'primary'); ?></label>
              </div>
              <div class="separator"></div>
            </div>
          </div>
          </form>
          <div class="modal-footer">
            <div class="row col-md-12" style="width:30rem;">
              <div class="col-md-6">
                <label for="buttononlineCatalog"><a href="../index.php">
                  <button class="btn text-start" data-bs-dismiss="modal"
                          aria-hidden="true"><?php echo CLICSHOPPING::getDef('header_title_online_catalog'); ?></button>
                  </a></label>
              </div>
              <div class="col-md-6">
                <label for="buttonNewPassword"><a href="<?php echo CLICSHOPPING::link('login.php', 'action=password'); ?>">
                  <button class="btn text-end" data-bs-dismiss="modal"
                          aria-hidden="true"><?php echo CLICSHOPPING::getDef('text_new_text_password'); ?></button>
                  </a></label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
<?php
    }
  } else {
?>
    <div id="loginModal" tabindex="-1" role="document" aria-hidden="true" style="padding-top:10rem;">
      <div class="modal-dialog">
        <div class="modal-content" style="background-color: transparent; border: none; align-items: center;">
          <?php echo HTML::form('send_password', CLICSHOPPING::link('login.php', 'action=send_password')); ?>
          <div class="modal-header">
            <h2 style="color:#233C7A;"><?php echo CLICSHOPPING::getDef('heading_title_sent_password'); ?></h2>
          </div>
          <div class="modal-body" style="width:30rem; padding-top:3rem;">
            <div class="col-md-12 center-block">
              <div class="text-danger"
                   style="font-size:12px; padding-bottom:10px;"><?php echo CLICSHOPPING::getDef('text_sent_password'); ?></div>
              <div class="input-group">
                <span class="input-group-addon" id="basic-addon1">@</span>
                <?php echo HTML::inputField('username', '', 'size="150" placeholder="' . CLICSHOPPING::getDef('text_email_lost_password') . '" required aria-required="true" autocomplete="off" aria-describedby="basic-addon1"'); ?>
              </div>
              <div class="separator"></div>
            </div>
          </div>
          <div class="row col-md-12">
            <div class="col-md-6">
              <label for="buttonHeaderAdministration"><a href="<?php echo CLICSHOPPING::link('login.php'); ?>">
                <button class="btn btn-secondary text-start"
                        type="button"><?php echo CLICSHOPPING::getDef('header_title_administration'); ?></button>
                </a></label>
            </div>
            <div class="col-md-6 text-end">
              <label for="buttonSubmit"><?php echo HTML::button(CLICSHOPPING::getDef('button_submit'), null, null, 'primary'); ?></label>
            </div>
          </div>
        </div>
        </form>
        <div class="separator"></div>
      </div>
    </div>
<?php
  }
?>
  <div class="clearfix"></div>
<?php
  require_once($CLICSHOPPING_Template->getTemplateHeaderFooterAdmin('footer.php'));
  require_once($CLICSHOPPING_Template->getTemplateHeaderFooterAdmin('application_bottom.php'));
