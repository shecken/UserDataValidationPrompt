<#1>
<?php
require_once("Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserDataValidationPrompt/classes/ilDB.php");
$settings_db = new \CaT\Plugins\UserDataValidationPrompt\ilDB($ilDB);
$settings_db->install();
?>
