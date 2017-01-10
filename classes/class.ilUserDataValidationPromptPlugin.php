<?php

/* Copyright (c) 2016, Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");

use CaT\Plugins\UserDataValidationPrompt;

require_once( __DIR__ ."/ilDB.php");
require_once( __DIR__ ."/ilSettings.php");
require_once( __DIR__ ."/ilActions.php");

/**
 * Create a form in an overlay to have the user validate his/her personal data.
 */
class ilUserDataValidationPromptPlugin extends ilUserInterfaceHookPlugin {
	function getPluginName() {
		return "UserDataValidationPrompt";
	}


	/**
	 * Get a closure to get txts from plugin.
	 *
	 * @return \Closure
	 */
	public function txtClosure() {
		return function($code) {
			return $this->txt($code);
		};
	}

	public function getActions() {
		if($this->actions === null) {
			global $ilUser;
				if($ilUser && (int)$ilUser->getId() !== 0) {
					$this->actions = new UserDataValidationPrompt\ilActions(
					$this->getUserDataValidationPromptDB(),
					$this->getSettings(),
					$this->getUserUtils((int)$ilUser->getId()),
					$this->getGevSettings()
				);
			}
		}

		return $this->actions;
	}

	protected function getUserUtils($user_id) {
		assert('is_int($user_id)');
		require_once('./Services/GEV/Utils/classes/class.gevUserUtils.php');
		return gevUserUtils::getInstance($user_id);
	}

	protected function getSettings() {
		if($this->settings === null) {
			$this->settings = new UserDataValidationPrompt\ilSettings();
		}

		return $this->settings;
	}

	protected function getUserDataValidationPromptDB() {
		if($this->user_data_val_prompt_db === null) {
			global $ilDB;
			$this->user_data_val_prompt_db = new UserDataValidationPrompt\ilDB($ilDB);
		}

		return $this->user_data_val_prompt_db;
	}

	protected function getGevSettings() {
		require_once('./Services/GEV/Utils/classes/class.gevSettings.php');
		return gevSettings::getInstance();
	}
}