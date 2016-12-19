<?php
/* Copyright (c) 2016, Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

use CaT\Plugins\UserdataValidation;
/**
 * Configuration GUI for the UserdataValidation plugin
 *
 */
class ilUserdataValidationConfigGUI extends ilPluginConfigGUI {

	const CMD_CONF_UI = "configure";
	const CMD_SAVE = "storePluginSettings";

	/**
	 * @var \template
	 */
	protected $gTpl;

	/**
	 * @var \ilCtrl
	 */
	protected $gCtrl;

	/**
	 * @var \Closure
	 */
	protected $txt;

	/**
	 * @var UserdataValidation\ilSettings
	 */
	protected $settings;


	public function __construct() {
		global $tpl, $ilCtrl, $ilToolbar;
		$this->gTpl = $tpl;
		$this->gCtrl = $ilCtrl;
		$this->settings = new UserdataValidation\ilSettings();
	}

	/**
	* Handles all commmands
	*
	* @param 	string 	$cmd
	*/
	public function performCommand($cmd) {
		$this->txt = $this->plugin_object->txtClosure();

		switch ($cmd) {
			case self::CMD_CONF_UI:
			case self::CMD_SAVE:
				$this->$cmd();
				break;
			default:
				throw new \Exception("ilUserdataValidationConfigGUI: CMD ".$cmd." not found");
		}
	}

	/**
	 * Configuration-GUI
	 *
	 * @param  	\ilPropertyFormGUI 	$form 	(optional)
	 */
	public function configure($form = null)	{
		if($form === null) {
			$form = $this->initForm();
		}

		$values = $this->settings->settings();
		$form->setValuesByArray($values);

		$form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
		$html = $form->getHtml();
		$this->gTpl->setContent($html);
	}

	/**
	 * validate and store post-values
	 *
	 */
	public function storePluginSettings() {
		$post = $_POST;
		$form = $this->initForm();

		if(!$form->checkInput()) {
			$form->setValuesByPost();
			\ilUtil::sendFailure($this->txt("not_saved"), true);
			$this->configure($form);
			return;
		}
		if(!$this->validateEntries($post, $form)) {
			$form->setValuesByPost();
			\ilUtil::sendFailure($this->txt("entries_not_valid"), true);
			$this->configure($form);
			return;
		}
		$this->settings->storeSettings($post);
		$this->gCtrl->redirect($this, self::CMD_CONF_UI);
	}

	/**
	 * create and return form
	 *
	 * @return 	\ilPropertyFormGUI
	 */
	private function initForm() {
		$form = new \ilPropertyFormGUI();

		$ni = new \ilNumberInputGUI($this->txt('interval'), UserdataValidation\ilSettings::F_INTERVAL);
		$ni->setRequired(true);
		$form->addItem($ni);

		$ta = new \ilTextAreaInputGUI($this->txt('description'), UserdataValidation\ilSettings::F_DESCRIPTION);
		$form->addItem($ta);

		$form->setFormAction($this->gCtrl->getFormAction($this));
		return $form;
	}

	/**
	 * validate given post-values and mark erroneous fields
	 *
	 * @param 	array 	$post
	 * @param 	\ilPropertyFormGUI 	$form
	 * @return 	boolean
	 */
	private function validateEntries($post, $form) {
		$res = true;
		$k = UserdataValidation\ilSettings::F_INTERVAL;
		if(! $this->settings->validateInterval($post[$k])) {
			$res = false;
			$item = $form->getItemByPostVar($k);
			$item->setAlert($this->txt('enter_positive_number'));
		}
		return $res;
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

}