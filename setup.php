<?php

// Init the hooks of the plugins -Needed
function plugin_init_autologin() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['autologin'] = true;
   
   $PLUGIN_HOOKS['display_login']['autologin'] = 'plugin_autologin_display_login';
}

// Get the name and the version of the plugin - Needed
function plugin_version_autologin() {
   return array(
       'name'           => 'Auto Login',
       'version'        => '1.0.0',
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
