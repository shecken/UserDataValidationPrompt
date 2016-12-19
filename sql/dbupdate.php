<#1>
<?php
require_once("Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserdataValidation/classes/ilDB.php");
$settings_db = new \CaT\Plugins\UserdataValidation\ilDB($ilDB);
$settings_db->install();
?>
