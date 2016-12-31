<?php

function plugin_autologin_install() {
   return true;
}

function plugin_autologin_uninstall() {
   return true;
}

function plugin_autologin_display_login() {
   global $CFG_GLPI;

   if (!Session::getLoginUserID()) {
      return false;
   }

// Redirect management
   $REDIRECT = "";
   if (isset($_POST['redirect']) && (strlen($_POST['redirect']) > 0)) {
      $REDIRECT = "?redirect=" . rawurlencode($_POST['redirect']);
   } else if (isset($_GET['redirect']) && strlen($_GET['redirect']) > 0) {
      $REDIRECT = "?redirect=" . rawurlencode($_GET['redirect']);
   }

   $url = false;

   if ($_SESSION["glpiactiveprofile"]["interface"] == "helpdesk") {
      if ($_SESSION['glpiactiveprofile']['create_ticket_on_login'] && empty($REDIRECT)) {
         $url = $CFG_GLPI['root_doc'] . "/front/helpdesk.public.php?create_ticket=1";
      } else {
         $url = $CFG_GLPI['root_doc'] . "/front/helpdesk.public.php$REDIRECT";
      }
   } else {
      if ($_SESSION['glpiactiveprofile']['create_ticket_on_login'] && empty($REDIRECT)) {
         $url = $CFG_GLPI['root_doc'] . "/front/ticket.form.php";
      } else {
         $url = $CFG_GLPI['root_doc'] . "/front/central.php$REDIRECT";
      }
   }

   echo "\n<!-- Begin AutoLogin -->\n";
   echo "<div style=\"text-align: center\">\n";
   echo "<img src=\"" . $CFG_GLPI['root_doc'] . "/plugins/autologin/pics/loading.gif\" />\n";
   echo "</div>\n";
   echo "<script type=\"text/javascript\">\n";
   echo "document.getElementById('login_name').disabled=true;\n";
   echo "document.getElementById('login_name').style.backgroundColor=\"#DDD\";\n";
   echo "document.getElementById('login_password').disabled=true;\n";
   echo "document.getElementById('login_password').style.backgroundColor=\"#DDD\";\n";
   echo "window.location = " . json_encode($url) . ";\n";
   echo "</script>\n";
   echo "<!-- End AutoLogin -->\n";
   return true;
}
