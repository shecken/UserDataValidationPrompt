<?php

/* Copyright (c) 2016, Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");

/**
 * Create a form in an overlay to have the user validate his/her personal data.
 */
class ilUserdataValidationPlugin extends ilUserInterfaceHookPlugin {
	function getPluginName() {
		return "UserdataValidation";
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
}

