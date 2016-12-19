<?php

/* Copyright (c) 2016, Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");
require_once( __DIR__ ."/ilDB.php");
require_once( __DIR__ ."/ilSettings.php");
require_once( __DIR__ ."/ilActions.php");
//require_once "./Services/User/classes/class.ilObjUserGUI.php";
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

use CaT\Plugins\UserdataValidation;

/**
 * Create a form in an overlay to have the user validate his/her personal data.
 *
 * @ilCtrl_Calls ilUserdataValidationUIHookGUI: ilFormPropertyDispatchGUI
 */
class ilUserdataValidationUIHookGUI extends ilUIHookPluginGUI {

	const CMD_UPDATEUSERDATA = "updateUserData";

	/**
	 * @var \Closure
	 */
	protected $txt;

	/**
	 * @var UserdataValidation\ilActions
	 */
	protected $actions;

	public function __construct() {
		global $ilUser, $ilDB, $lng, $ilCtrl;
		$this->gUser = $ilUser;
		$this->gDB = $ilDB;
		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->initActions();
	}

	/**
	 * initialize actions for plugin
	 */
	private function initActions() {
		$db = new UserdataValidation\ilDB($this->gDB);
		$settings = new UserdataValidation\ilSettings();
		$this->actions = new UserdataValidation\ilActions($db, $settings);
	}

	/**
	 * build userdata form
	 */
	private function initUserDataForm() {
		$form = new \ilPropertyFormGUI();
		$fields = array(
			'firstname' => $this->gUser->getFirstname(),
			'lastname' => $this->gUser->getLastname(),
			'birthday' => $this->gUser->getBirthday(),
			'street' => $this->gUser->getStreet(),
			'zipcode' => $this->gUser->getZipcode(),
			'city' => $this->gUser->getCity(),
		);
		$optional = array('street', 'zipcode', 'city');

		foreach($fields as $field => $value) {
			switch($field) {
				case 'birthday':
					$inp = new ilBirthdayInputGUI($this->gLng->txt($field), $field);
					$inp->setShowEmpty(true);
					$inp->setStartYear(1900);
					break;
				default:
					$inp = new ilTextInputGUI($this->gLng->txt($field), $field);
					$inp->setSize(32);
					$inp->setMaxLength(32);

			}
			if(! in_array($field, $optional)){
				$inp->setRequired(true);
			}
			$form->addItem($inp);
		}
		$form->setValuesByArray($fields);

		$inp = new ilHiddenInputGUI('udvalidation', 'udvalidation');
		$form->addItem($inp);
		$inp->setValue('udvalidation_update');

		$form->addCommandButton(self::CMD_UPDATEUSERDATA, $this->txt("update"));

		//$form->setFormAction($this->gCtrl->getFormAction($this->plugin_object));
		//$form->setFormAction('./');

		return $form;
	}


	/**
	 * @param 	string	$code
	 * @return	string
	 */
	protected function txt($code) {
		assert('is_string($code)');
		$txt = $this->txt;
		return $txt($code);
	}


	/**
	 * @inheritdoc
	 */
	function getHTML($a_comp, $a_part, $a_par = array()) {

		if ( 	$a_part != "template_get"
			|| 	$a_par["tpl_id"] != "Services/MainMenu/tpl.main_menu.html"
			||	$this->actions->sessionStatus($this->gUser->getId())
		   ) {
			return parent::getHTML($a_comp, $a_part, $a_par);
		}

		if($this->actions->shouldUserUpdate($this->gUser->getId()) === false) {
			//interval not reached, do not bother again for this session
			$this->actions->validateSession($this->gUser->getId());
			return parent::getHTML($a_comp, $a_part, $a_par);
		}

		if($_POST && @$_POST['udvalidation'] === 'udvalidation_update') {
			var_dump($_POST);
			//die();
		}

		//user should update, session is not set: show dialog
		$this->txt = $this->plugin_object->txtClosure();
		$description = $this->actions->pluginSettingsDescription();
		$form = $this->initUserDataForm();
		$formhtml = $form->getHtml();

/*
		$lnk = $this->gCtrl->getLinkTargetByClass(strtolower(get_class($this)), self::CMD_UPDATEUSERDATA, '', false, false);
var_dump($lnk);
die();
		$script = $this->getLinkTargetByClass(strtolower(get_class($a_gui_obj)), $a_cmd,
			"", $a_asynch, false);
*/

		// TODO: This should totally go to a template:
		$ann = <<<HTML

<div class="gev_ann" style="position: fixed; background-color: #000000; left:0; top:0; height:100%; width:100%; opacity: 0.5; visibility: visible; display: block;">

</div>
<div  class="gev_ann" style="z-index: 1000; background-color: #CECECE; opacity: 1; margin: -350px 0 0 -400px; width: 800px; height: 700px; position: absolute; top:50%; left: 50%; overflow: hidden;" >
	<!--div style="float: right; margin-top: 15px; margin-bottom: 5px; margin-right: 15px;">
			<a id="gev_ann_close" href="#">Schließen (X)</a>
	</div-->
	<div class="ilClearFloat"></div>
	<div class="catTitle" style="background-color: #FFFFFF; padding: 10px;">
		<div>
			<div class="catTitleTextContentsWrapper">
				<div>
					<h1 class="catTitleHeader">Überprüfen Sie Ihre Daten!</h1>
					$description
					$formhtml
				</div>
			</div>
		</div>
	</div>
</div>

HTML;

		return array
			( "mode" => ilUIHookPluginGUI::APPEND
			, "html" => $ann
			);
	}
}
