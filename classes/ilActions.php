<?php
/* Copyright (c) 2016, Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\UserDataValidationPrompt;
require_once(__DIR__ .'/ilSettings.php');

/**
 * Actions for the UserDataValidationPrompt-Plugin
 *
 */
class ilActions {
	/**
	 * @var UserDataValidationPrompt\ilDB
	 */
	protected $db;

	/**
	 * @var UserDataValidationPrompt\ilSettings
	 */
	protected $settings;

	/**
	 * @var \gevUserUtils
	 */
	protected $uutils;

	/**
	 * @var \gevSettings
	 */
	protected $gev_settings;


	public function __construct($db, $settings, $user_utils, $gev_settings) {
		$this->db = $db;
		$this->settings = $settings;
		$this->uutils = $user_utils;
		$this->gev_settings = $gev_settings;
	}

	/**
	 * Returns the interval-setting
	 *
	 * @return 	int
	 */
	public function pluginSettingsInterval() {
		$settings = $this->settings->settings();
		return $settings[ilSettings::F_INTERVAL];
	}

	/**
	 * Returns the title-text to be shown in the dialog
	 *
	 * @return 	string
	 */
	public function pluginSettingsTitle() {
		$settings = $this->settings->settings();
		return $settings[ilSettings::F_TITLE];
	}

	/**
	 * Returns the description-text to be shown in the dialog
	 *
	 * @return 	string
	 */
	public function pluginSettingsDescription() {
		$settings = $this->settings->settings();
		return $settings[ilSettings::F_DESCRIPTION];
	}

	/**
	 * Returns the time the user has last updated his/her credentials
	 * via this plugin
	 *
	 * @return 	\ilDate
	 */
	public function lastUpdateOfUser($usr_id) {
		$dat = $this->db->read($usr_id);
		return $dat;
	}

	/**
	 * Save the time, the user has last updated his/her data
	 * (which is now..)
	 */
	public function storeLastUpdateOfUser($usr_id) {
		$this->db->update($usr_id);
	}

	/**
	 * check session (cookie) to determine if the user was presented
	 * with the dialog already during this session
	 *
	 * @param  	int 	$usr_id
	 * @return 	boolean
	 */
	public function sessionStatus($usr_id) {
		return $_COOKIE["gevudvp"][$usr_id] === "gevudvp";
	}

	/**
	 * set a session-cookie to not check this user again during his/her session
	 *
	 * @param  	int 	$usr_id
	 */
	public function validateSession($usr_id) {
		setcookie("gevudvp[".$usr_id."]", "gevudvp");
	}

	/**
	 * decide by user_id and settings (interval), if the user should
	 * validate his/her personal data
	 *
	 * @param  	int 	$usr_id
	 * @return 	boolean
	 */
	public function shouldUserUpdate($usr_id) {
		if(in_array($usr_id, $this->getToIgnoreUserIds())) {
			return false;
		}

		include_once('Services/Calendar/classes/class.ilDateTime.php');
		$lastup = substr($this->lastUpdateOfUser($usr_id), 0, 10);
		$last = new \ilDate($lastup, IL_CAL_DATE);
		$now = new \ilDate(time(),IL_CAL_UNIX);

		$interval = $this->pluginSettingsInterval();
		$due = new \ilDate(
			$last->increment(\ilDateTime::DAY, $interval),
			IL_CAL_UNIX
		);
		return $due->get(IL_CAL_UNIX) <= $now->get(IL_CAL_UNIX);
	}

	/**
	* get UDF-Value from user-utils
	*
	* @return string
	*/
	public function udfPrivateStreet() {
		return $this->uutils->getPrivateStreet();
	}

	/**
	* get UDF-Value from user-utils
	*
	* @return string
	*/
	public function udfPrivateZipcode() {
		return $this->uutils->getPrivateZipcode();
	}

	/**
	* get UDF-Value from user-utils
	*
	* @return string
	*/
	public function udfPrivateCity() {
		return $this->uutils->getPrivateCity();
	}

	/**
	* update user data and udf
	*
	* @param array $userdata
	*/
	public function updateUserData($userdata) {
		$usr = $this->uutils->getUser();
		$usr->setFirstname(trim($userdata['firstname']));
		$usr->setLastname(trim($userdata['lastname']));
		$usr->setStreet(trim($userdata['street']));
		$usr->setZipcode(trim($userdata['zipcode']));
		$usr->setCity(trim($userdata['city']));
		$usr->setEmail(trim($userdata['email']));
		$usr->update();
		$this->uutils->setPrivateStreet(trim($userdata['p_street']));
		$this->uutils->setPrivateCity(trim($userdata['p_city']));
		$this->uutils->setPrivateZipcode(trim($userdata['p_zipcode']));

	}

	/**
	 * Get user ids of user to ignore
	 *
	 * @return int[]
	 */
	protected function getToIgnoreUserIds() {
		return array(0, $this->gev_settings->getAgentOfferUserId());
	}
}