<?php
/* Copyright (c) 2016 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see ./LICENSE */

namespace CaT\Plugins\UserdataValidation;

/**
 * persistence implementation of last data-update for users.
 */
class ilDB {
	/**
	 * @var gDB \ilDB
	 */
	private $gDB;

	const TABLE_UDVLASTUPDATE = 'gevudv_lastupdate';

	public function __construct(\ilDB $db) {
		$this->gDB = $db;
	}

	/**
	 * Install DB table
	 */
	public function install() {
		$this->installTable();
	}

	/**
	 * Update or insert a usr_id with current timestamp
	 *
	 * @param int $usr_id
	 */
	public function update($usr_id){
		assert('is_int($usr_id)');
		$query = 'REPLACE INTO '
			.static::TABLE_UDVLASTUPDATE
			.' (usr_id, lastupdate)'
			.' VALUES ('
			.$this->gDB->quote($usr_id, 'integer')
			.', NOW()'
			.')';
		$this->gDB->manipulate($query);

	}

	/**
	 * get last update of user-data (in scope of this plugin) for user id
	 *
	 * @param int $usr_id
	 * @return string
	 */
	public function read($usr_id) {
		assert('is_int($usr_id)');
		$query = 'SELECT lastupdate	FROM '
			.static::TABLE_UDVLASTUPDATE
			.' WHERE usr_id = '
			.$this->gDB->quote($usr_id, 'integer');

		$res = $this->gDB->query($query);
		if($this->gDB->numRows($res) == 0) {
			return '1970-01-01 00:00:00';
		}
		return $this->gDB->fetchAssoc($res)['lastupdate'];
	}

	/**
	 * install the table for usr/last update
	 */
	protected function installTable() {
		$fields = array(
			'usr_id' => array(
				'type' => 'integer',
				'length' => 8,
				'notnull' => true
			),
			'lastupdate' => array(
				'type' => 'timestamp',
				'notnull' => true
			)
		);
		if(!$this->gDB->tableExists(static::TABLE_UDVLASTUPDATE)) {
			$this->gDB->createTable(static::TABLE_UDVLASTUPDATE, $fields);
			$this->gDB->addPrimaryKey(static::TABLE_UDVLASTUPDATE, array('usr_id'));
		}
	}


}
