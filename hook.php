<?php

function plugin_autologin_install() {
   return true;
}

function plugin_autologin_uninstall() {
   return true;
}

function plugin_autologin_display_login() {
   echo "\n<!-- Begin AutoLogin -->\n";
   echo "<script type=\"text/javascript\">\n";
   echo "(function() {\n";

   if (version_compare(GLPI_VERSION, '0.90', '>=')) {
      echo "var passwordLine = document.getElementById('login_password').parentElement;\n";
      echo "var newLine = document.createElement('p');\n";
      echo "    newLine.className = 'login_input';\n";
      echo "    newLine.innerHTML = '<label style=\"color: #FCFCFC;\">"
      . "<input type=\"checkbox\" name=\"rememberme\" id=\"rememberme\" /> "
      . __('Remember me', 'autologin') . "</label>';\n";
      echo "passwordLine.parentElement.insertBefore(newLine, passwordLine.nextSibling);\n";
   } else {
      //GLPI <= 0.85
      echo "var passwordLine = document.getElementById('login_password').parentElement.parentElement;\n";

      echo "var newLine = document.createElement('div');\n";
      echo "    newLine.className = 'loginrow';\n";
      echo "    newLine.innerHTML = '<span class=\"loginlabel\"><label>" . __('Remember me', 'autologin') . "</label></span>"
      . "<span class=\"loginformw\" style=\"margin-right: 140px;\"><input type=\"checkbox\" name=\"rememberme\" id=\"rememberme\"\" /></span>';\n";
      echo "passwordLine.parentElement.insertBefore(newLine, passwordLine.nextSibling);\n";
   }

   echo "})();\n";
   echo "</script>\n";
   echo "<!-- End AutoLogin -->\n";
   return true;
}

function plugin_autologin_init_session() {
   //If checkbox is not marked, not generate cookie
   if (!isset($_POST['rememberme'])) {
      return;
   }

   //Cookie name (Allow multiple GLPI)
   $cookie_name = session_name() . '_rememberme';
   //Remember-me duration after login
   $cookie_duration = 60 * 60 * 24 * 7; //7 days
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
             $cookie_duration,
         ]);

         //Send cookie to browser
         setcookie($cookie_name, $data, time() + $cookie_duration, $cookie_path);
         $_COOKIE[$cookie_name] = $data;
      }
   }
}
