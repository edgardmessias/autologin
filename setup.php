<?php

// Init the hooks of the plugins -Needed
function plugin_init_autologin() {
   global $CFG_GLPI, $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['autologin'] = true;

   if (Session::getLoginUserID() && isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], 'index.php') !== false) {
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
      return;
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_autologin() {
   return array(
      'name'           => 'Auto Login',
      'version'        => '1.1.0',
      'author'         => 'Edgard Lorraine Messias',
      'license'        => 'GPLv2+',
      'homepage'       => 'https://github.com/edgardmessias/autologin',
      'minGlpiVersion' => '0.85'
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_autologin_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '0.85', 'lt')) {
      echo "This plugin requires GLPI >= 0.85";
      return false;
   } else {
      return true;
   }
}

function plugin_autologin_check_config() {
   return true;
}
