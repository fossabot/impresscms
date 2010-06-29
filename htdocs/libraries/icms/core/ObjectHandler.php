<?php
/**
 * Manage of original Objects
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		LICENSE.txt
 * @category	ICMS
 * @package		Object
 * @version		SVN: $Id: object.php 19419 2010-06-13 22:52:12Z skenow $
 */

/**
 * Abstract object handler class.
 *
 * This class is an abstract class of handler classes that are responsible for providing
 * data access mechanisms to the data source of its corresponsing data objects
 *
 * @category	ICMS
 * @package		Object
 * @abstract
 *
 * @author  Kazumi Ono <onokazu@xoops.org>
 */
abstract class icms_core_ObjectHandler {

	/**
	 * holds referenced to {@link XoopsDatabase} class object
	 *
	 * @var object
	 * @see XoopsDatabase
	 * @access protected
	 */
	protected $db;

	//
	/**
	* called from child classes only
	*
	* @param object $db reference to the {@link XoopsDatabase} object
	* @access protected
	*/
	public function __construct(&$db) {
		$this->db =& $db;
	}

	/**
	 * creates a new object
	 *
	 * @abstract
	 */
	function &create() {

	}

	/**
	 * gets a value object
	 *
	 * @param int $int_id
	 * @abstract
	 */
	function &get($int_id) {

	}

	/**
	 * insert/update object
	 *
	 * @param object $object
	 * @abstract
	 */
	function insert(&$object) {

	}

	/**
	 * delete object from database
	 *
	 * @param object $object
	 * @abstract
	 */
	function delete(&$object) {

	}

}

