<?php
/* Copyright (c) 2016, Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\UserDataValidationPrompt;

/*
 * implementation of plugin-configuration (settings) for UserDataValidationPrompt
 */
class ilSettings {

	const SETTINGS_MODULE = "gevudvp";
	const F_INTERVAL = "interval";
	const F_TITLE = "title";
	const F_DESCRIPTION = "description";

	/**
	 * @var \ilSetting
	 */
	protected $settings;

	public function __construct() {
		$this->settings = new \ilSetting(self::SETTINGS_MODULE);
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$this->gev_settings = \gevSettings::getInstance();
	}

	/**
	 * store settings in global settings-table
	 *
	 * @param 	array 	$post
	 * @throws 	InvalidArgumentException
	 */
	public function storeSettings($post) {
		if(! $this->validateInterval($post[self::F_INTERVAL])) {

			throw new \InvalidArgumentException('UserDataValidationPrompt.ilSettings: interval must be apositive number', 1);
		}
		$this->settings->set(self::F_INTERVAL, $post[self::F_INTERVAL]);
		$this->settings->set(self::F_TITLE, $post[self::F_TITLE]);
		$this->settings->set(self::F_DESCRIPTION, $post[self::F_DESCRIPTION]);
	}

	/**
	 * returns setting values as assoc-array
	 *
	 * @return 	array
	 */
	public function settings() {
		return array(
			self::F_INTERVAL => $this->settings->get(self::F_INTERVAL, 365),
			self::F_TITLE => $this->settings->get(self::F_TITLE, ''),
			self::F_DESCRIPTION => $this->settings->get(self::F_DESCRIPTION, '')
		);
	}

	/**
	 * validate interval; must be positive number
	 *
	 * @param 	mixed $val
	 * @return 	boolean
	 */
	public function validateInterval($val) {
		if(is_nan($val) || intval($val) < 1) {
			return false;
		}
		return true;
	}

	/**
	 * Get user ids of user to ignore
	 *
	 * @return int[]
	 */
	public function getToIgnoreUserIds() {
		return array($this->gev_settings->getAgentOfferUserId());
	}
}