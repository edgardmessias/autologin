<?php

class PluginAutologinConfig extends CommonDBTM {

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Config':
            return array(1 => __('Auto Login', 'autologin'));
         default:
            return '';
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Config':
            $config = new self();
            $config->showConfigForm();
            break;
      }
      return true;
   }

   public static function configUpdate($input) {
      unset($input['_no_history']);
      return $input;
   }

   /**
    * Print the config form for display
    *
    * @return Nothing (display)
    * */
   function showConfigForm() {
      global $CFG_GLPI, $CFG_AUTOLOGIN;

      if (!Config::canView()) {
         return false;
      }
      $canedit = Session::haveRight(Config::$rightname, UPDATE);
      if ($canedit) {
         echo "<form name='form' action=\"" . Toolbox::getItemTypeFormURL('Config') . "\" method='post'>";
      }
      echo Html::hidden('config_context', ['value' => 'autologin']);
      echo Html::hidden('config_class', ['value' => __CLASS__]);

      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='4'>" . __('General setup') . "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Time to allow "Remember Me"', 'autologin') .
      "</td><td>";
      Dropdown::showTimeStamp('login_remember_time', array(
          'value'      => (int) $CFG_AUTOLOGIN["login_remember_time"],
          'emptylabel' => __('Disabled'),
          'min'        => 0,
          'max'        => MONTH_TIMESTAMP * 2,
          'step'       => DAY_TIMESTAMP));
      echo "<td>" . __("Default state of checkbox", 'autologin') . "</td><td>";
      Dropdown::showYesNo("login_remember_default", $CFG_AUTOLOGIN["login_remember_default"]);
      echo "</td>";
      echo "</td></tr>";

      if ($canedit) {
         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='4' class='center'>";
         echo "<input type='submit' name='update' class='submit' value=\"" . _sx('button', 'Save') . "\">";
         echo "</td></tr>";
      }

      echo "</table></div>";
      Html::closeForm();
   }

}
