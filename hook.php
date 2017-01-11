<?php

function plugin_autologin_install() {

   $current_config = Config::getConfigurationValues('autologin');

   if (!isset($current_config['login_remember_time'])) {
      $current_config['login_remember_time'] = HOUR_TIMESTAMP * 7;
   }

   if (!isset($current_config['login_remember_default'])) {
      $current_config['login_remember_default'] = 1;
   }

   Config::setConfigurationValues('autologin', $current_config);

   return true;
}

function plugin_autologin_uninstall() {
   Config::deleteConfigurationValues('autologin');

   return true;
}

function plugin_autologin_display_login() {
   global $CFG_AUTOLOGIN;
   echo "\n<!-- Begin AutoLogin -->\n";
   echo "<script type=\"text/javascript\">\n";
   echo "(function() {\n";

   if (version_compare(GLPI_VERSION, '0.90', '>=')) {
      echo "var passwordLine = document.getElementById('login_password').parentElement;\n";
      echo "var newLine = document.createElement('p');\n";
      echo "    newLine.className = 'login_input';\n";
      echo "    newLine.innerHTML = '<label style=\"color: #FCFCFC;\">"
      . "<input type=\"checkbox\" name=\"rememberme\" id=\"rememberme\""
      . ($CFG_AUTOLOGIN['login_remember_default'] ? ' checked="cheched"' : '') . " /> "
      . __('Remember me', 'autologin') . "</label>';\n";
      echo "passwordLine.parentElement.insertBefore(newLine, passwordLine.nextSibling);\n";
   } else {
      //GLPI <= 0.85
      echo "var passwordLine = document.getElementById('login_password').parentElement.parentElement;\n";

      echo "var newLine = document.createElement('div');\n";
      echo "    newLine.className = 'loginrow';\n";
      echo "    newLine.innerHTML = '<span class=\"loginlabel\"><label>" . __('Remember me', 'autologin') . "</label></span>"
      . "<span class=\"loginformw\" style=\"margin-right: 140px;\">"
      . "<input type=\"checkbox\" name=\"rememberme\" id=\"rememberme\"\""
      . ($CFG_AUTOLOGIN['login_remember_default'] ? ' checked="cheched"' : '') . " /> "
      . "</span>';\n";
      echo "passwordLine.parentElement.insertBefore(newLine, passwordLine.nextSibling);\n";
   }

   echo "})();\n";
   echo "</script>\n";
   echo "<!-- End AutoLogin -->\n";
   return true;
}

function plugin_autologin_init_session() {
   global $CFG_AUTOLOGIN;

   //If checkbox is not marked, not generate cookie
   if (!isset($_POST['rememberme'])) {
      return;
   }

   //Cookie name (Allow multiple GLPI)
   $cookie_name = session_name() . '_rememberme';
   //Remember-me duration after login
   $cookie_duration = $CFG_AUTOLOGIN['login_remember_time']; //7 days
   //Cookie session path
   $cookie_path = ini_get('session.cookie_path');

   if (Session::getLoginUserID() && !isset($_COOKIE[$cookie_name])) {
      $uid = Session::getLoginUserID();

      $token = User::getPersonalToken($uid);

      if ($token) {
         $hash = Auth::getPasswordHash($token);

         $data = json_encode([
             $uid,
             $hash,
             time() + $cookie_duration,
         ]);

         //Send cookie to browser
         setcookie($cookie_name, $data, time() + $cookie_duration, $cookie_path);
         $_COOKIE[$cookie_name] = $data;
      }
   }
}
