<?php

/* Copyright (c) 2016, Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");
require_once( __DIR__ ."/ilDB.php");
require_once( __DIR__ ."/ilSettings.php");
require_once( __DIR__ ."/ilActions.php");
//require_once "./Services/User/classes/class.ilObjUserGUI.php";
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/GEV/Utils/classes/class.gevUserUtils.php');

use CaT\Plugins\UserDataValidationPrompt;

/**
 * Create a form in an overlay to have the user validate his/her personal data.
 *
 */
class ilUserDataValidationPromptUIHookGUI extends ilUIHookPluginGUI {

	const CMD_UPDATEUSERDATA = "updateUserData";

	/**
	 * @var \Closure
	 */
	protected $txt;

	/**
	 * @var UserDataValidationPrompt\ilActions
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
		if(	$this->gUser && $this->gUser->getId() != 0) {
			$db = new UserDataValidationPrompt\ilDB($this->gDB);
			$settings = new UserDataValidationPrompt\ilSettings();
			$user_utils = gevUserUtils::getInstance($this->gUser->getId());
			$this->actions = new UserDataValidationPrompt\ilActions($db, $settings, $user_utils);
		}
	}

	/**
	 * build userdata form
	 */
	private function initUserDataForm() {
		$form = new \ilPropertyFormGUI();
		$formitems = array(
			'generic' => array(
				'firstname' => $this->gUser->getFirstname(),
				'lastname' => $this->gUser->getLastname(),
			),
			'buiz' => array(
				'street' => $this->gUser->getStreet(),
				'zipcode' => $this->gUser->getZipcode(),
				'city' => $this->gUser->getCity(),
			),
			'priv' => array( //from user utils!
				'p_street' => $this->actions->udfPrivateStreet(),
				'p_zipcode' => $this->actions->udfPrivateZipcode(),
				'p_city' => $this->actions->udfPrivateCity(),
			)

		);
		$optional = array('p_street', 'p_zipcode', 'p_city');
		$section_titles = array(
			'generic' => 'gev_personal_data',
			'buiz' => 'gev_business_contact',
			'priv' => 'gev_private_contact'
		);

		$fieldvalues = array();
		foreach($formitems as $section => $fields) {
			$frmsection = new ilFormSectionHeaderGUI();
			$frmsection->setTitle($this->gLng->txt($section_titles[$section]));
			$form->addItem($frmsection);

			foreach($fields as $field => $value) {
				switch($field) {
					default:
						$label = $field;
						if(substr($label, 0, 2) === 'p_') {
							$label = substr($label, 2);
						}
						$inp = new ilTextInputGUI($this->gLng->txt($label), $field);
						$inp->setSize(32);
						$inp->setMaxLength(32);

				}
				if(! in_array($field, $optional)){
					$inp->setRequired(true);
				}
				$form->addItem($inp);
				$fieldvalues[$field] = $value;
			}
		}

		$form->setValuesByArray($fieldvalues);

		$inp = new ilHiddenInputGUI('udvalidation', 'udvalidation');
		$form->addItem($inp);
		$inp->setValue('udvalidation_update');

		$form->setFormAction('');
		$form->addCommandButton(self::CMD_UPDATEUSERDATA, $this->gLng->txt("update"));

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
			||	$this->actions == null
			||	$this->actions->sessionStatus($this->gUser->getId())
		   ) {
			return parent::getHTML($a_comp, $a_part, $a_par);
		}

		if($this->actions->shouldUserUpdate($this->gUser->getId()) === false) {
			//interval not reached, do not bother again for this session
			$this->actions->validateSession($this->gUser->getId());
			return parent::getHTML($a_comp, $a_part, $a_par);
		}

		$this->txt = $this->plugin_object->txtClosure();
		$form = $this->initUserDataForm();

		//catch form-submission
		if($_POST && @$_POST['udvalidation'] === 'udvalidation_update') {
			$form->setValuesByPost();

			if($form->checkInput()) {
				//update data, do not bother again in this session
				$this->actions->updateUserData($_POST);
				$this->actions->storeLastUpdateOfUser($this->gUser->getId());
				$this->actions->validateSession($this->gUser->getId());
				return parent::getHTML($a_comp, $a_part, $a_par);

			} else {
				//validation failed, show errors:
				foreach ($validation as $field => $msg) {
					$form_field = $form->getItemByPostVar($field);
					$form_field->setAlert($this->gLng->txt($msg));
				}
			}
		}

		//user should update, session is not set or validation failed: show dialog
		$description = $this->actions->pluginSettingsDescription();
		$formhtml = $form->getHtml();


		// TODO: This should totally go to a template:
		$ann = <<<HTML

<div class="gev_ann" style="position: fixed; background-color: #000000; left:0; top:0; height:100%; width:100%; opacity: 0.5; visibility: visible; display: block;">
</div>
<div  class="gev_ann" style="z-index: 1000; background-color: #CECECE; opacity: 1; margin: -350px 0 0 -400px; width: 800px; height: 750px; position: absolute; top:50%; left: 50%; overflow: hidden;" >
	<div class="ilClearFloat"></div>
	<div class="catTitle" style="background-color: #FFFFFF; padding: 10px;">
		<div>
			<div class="catTitleTextContentsWrapper">
				<div>
					<h1 class="catTitleHeader">Überprüfen Sie Ihre Daten!</h1>
				</div>
				<div class="ilClearFloat catSubtitle" style="width:750px;">
					$description
				</div>
				$formhtml
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
