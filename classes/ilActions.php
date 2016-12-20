<?php
/* Copyright (c) 2016, Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\UserdataValidation;
require_once(__DIR__ .'/ilSettings.php');

/**
 * Actions for the UserdataValidation-Plugin
 *
 */
class ilActions {
	/**
	 * @var UserdataValidation\ilDB
	 */
	protected $db;

	/**
	 * @var UserdataValidation\ilSettings
	 */
	protected $settings;

	/**
	 * @var \gevUserUtils
	 */
	protected $uutils;


	public function __construct($db, $settings, $user_utils) {
		$this->db = $db;
		$this->settings = $settings;
		$this->uutils = $user_utils;
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
	 * Returns the description-text to be shown in the dialog
	 *
	 * @return 	string
	 */
	public function pluginSettingsDescription() {
		$settings = $this->settings->settings();
		return $settings[ilSettings::F_DESCRIPTION];
	}

	/**
	 * Returns the
	 *
	 * @return 	\ilDate
	 */
	public function lastUpdateOfUser() {

	}

	/**
	 * Save the time, the user has last updated his/her data
	 * (which is now..)
	 */
	public function storeLastUpdateOfUser() {

	}

	/**
	 * check session (cookie) to determine if the user was presented
	 * with the dialog already during this session
	 *
	 * @param  	int 	$usr_id
	 * @return 	boolean
	 */
	public function sessionStatus($usr_id) {
		return false;
		return $_COOKIE["gev_udvalidaton"][$usr_id] === "udvalidaton";
	}

	/**
	 * set a session-cookie to not check this user again during his/her session
	 *
	 * @param  	int 	$usr_id
	 */
	public function validateSession($usr_id) {
		//setcookie("gev_udvalidaton[".$usr_id."]", "udvalidaton", time()+31*24*3600);
		setcookie("gev_udvalidaton[".$usr_id."]", "udvalidaton");
	}

	/**
	 * decide by user_id and settings (interval), if the user should
	 * validate his/her personal data
	 *
	 * @param  	int 	$usr_id
	 * @return 	boolean
	 */
	public function shouldUserUpdate($usr_id) {
		return true;
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
		$bday = $userdata['birthday']["date"];

		$usr->setBirthday($bday);
		$usr->setFirstname($userdata['firstname']);
		$usr->setLastname($userdata['lastname']);
		$usr->setStreet($userdata['street']);
		$usr->setZipcode($userdata['zipcode']);
		$usr->setCity($userdata['city']);
		$this->uutils->setPrivateStreet($userdata['p_street']);
		$this->uutils->setPrivateCity($userdata['p_zipcode']);
		$this->uutils->setPrivateZipcode($userdata['p_city']);

		$usr->update();
	}

}
