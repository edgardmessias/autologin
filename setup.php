<?php

define('PLUGIN_AUTOLOGIN_VERSION', '2.1.1');

// Init the hooks of the plugins -Needed
function plugin_init_autologin() {
   global $CFG_GLPI, $PLUGIN_HOOKS, $CFG_AUTOLOGIN;

   $CFG_AUTOLOGIN = Config::getConfigurationValues('autologin');

   $PLUGIN_HOOKS['csrf_compliant']['autologin'] = true;

   if ($CFG_AUTOLOGIN['login_remember_time']) {
      $PLUGIN_HOOKS['display_login']['autologin'] = 'plugin_autologin_display_login';
      $PLUGIN_HOOKS['init_session']['autologin'] = 'plugin_autologin_init_session';
   }

   Plugin::registerClass('PluginAutologinConfig'
           , ['addtabon' => ['Config']]);

   //Cookie name (Allow multiple GLPI)
   $cookie_name = session_name() . '_rememberme';

   //Cookie session path
   $cookie_path = ini_get('session.cookie_path');

   //For logout, remove COOKIE to prevent automatic login on open index page
   if (Session::getLoginUserID() && isset($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] === rtrim($CFG_GLPI['root_doc'], '/') . '/front/logout.php') {
      setcookie($cookie_name, '', time() - 3600, $cookie_path);
      return;
   }

   //If not logged and has valid cookie, start login
   if (!Session::getLoginUserID() && isset($_COOKIE[$cookie_name]) && $CFG_AUTOLOGIN['login_remember_time']) {

      $auth_succeded = false;

      $data = json_decode($_COOKIE[$cookie_name], true);
      if (count($data) === 3) {
         list ($cookie_id, $cookie_token, $cookie_expiration) = $data;

         $is_expiration_valid = $cookie_expiration < time() + $CFG_AUTOLOGIN['login_remember_time'];

         $token = User::getPersonalToken($cookie_id);

         if ($is_expiration_valid && $token !== false && Auth::checkPassword($token, $cookie_token)) {
            $user = new User();
            $user->getFromDB($cookie_id); //true if $token is not false
            //Create fake auth
            $auth = new Auth();
            $auth->user = $user;
            $auth->auth_succeded = true;
            $auth->extauth = 1;
            $auth->user_present = $auth->user->getFromDBbyName(addslashes($user->fields['name']));
            $auth->user->fields['authtype'] = Auth::DB_GLPI;

            Session::init($auth);

            $auth_succeded = $auth->auth_succeded;
         }
      }

      //remove COOKIE for invalid cookie
      //remove COOKIE for invalid token
      //remove COOKIE for invalid account
      //remove COOKIE for expiration > time() + login_remember_time
      if (!$auth_succeded) {
         setcookie($cookie_name, '', time() - 3600, $cookie_path);
         unset($_COOKIE[$cookie_name]);
      }
   }

   //Redirect from login to front page if is authenticated

   if (!isset($_GET["noAUTO"]) &&
           Session::getLoginUserID() &&
           isset($_SERVER['SCRIPT_NAME']) &&
           $_SERVER['SCRIPT_NAME'] === rtrim($CFG_GLPI['root_doc'], '/') . '/index.php') {
      $REDIRECT = "";
      if (isset($_POST['redirect']) && (strlen($_POST['redirect']) > 0)) {
         $REDIRECT = "?redirect=" . rawurlencode($_POST['redirect']);
      } else if (isset($_GET['redirect']) && strlen($_GET['redirect']) > 0) {
         $REDIRECT = "?redirect=" . rawurlencode($_GET['redirect']);
      }

      if ($_SESSION["glpiactiveprofile"]["interface"] == "helpdesk") {
         if ($_SESSION['glpiactiveprofile']['create_ticket_on_login'] && empty($REDIRECT)) {
            Html::redirect($CFG_GLPI['root_doc'] . "/front/helpdesk.public.php?create_ticket=1");
         }
         Html::redirect($CFG_GLPI['root_doc'] . "/front/helpdesk.public.php$REDIRECT");
      } else {
         if ($_SESSION['glpiactiveprofile']['create_ticket_on_login'] && empty($REDIRECT)) {
            Html::redirect($CFG_GLPI['root_doc'] . "/front/ticket.form.php");
         }
         Html::redirect($CFG_GLPI['root_doc'] . "/front/central.php$REDIRECT");
      }
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_autologin() {
   return array(
       'name'           => __('Auto Login', 'autologin'),
       'version'        => PLUGIN_AUTOLOGIN_VERSION,
       'author'         => 'Edgard Lorraine Messias',
       'license'        => 'GPLv2+',
       'homepage'       => 'https://github.com/edgardmessias/autologin',
       'minGlpiVersion' => '0.85'
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_autologin_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '0.85', 'lt')) {
      echo __('This plugin requires GLPI >= 0.85', 'autologin');
      return false;
   } else {
      return true;
   }
}

function plugin_autologin_check_config() {
   return true;
}
