<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Table.php 261 2011-07-22 13:25:04Z beimuaihui@gmail.com $
 */
/**
 * @author 
 * @version
 */

Zend_Loader::loadClass('Zend_Db_Table_Abstract');

//class Permission extends Zend_Db_Table_Abstract {
namespace Baogg\Db\Table;
class Qa  extends Baogg\Db\Table {
	/**
	 * The default table name 
	 */
	protected $_name;
	protected $_db ;
	protected $_primary; 
	
	function __construct() {	   
	    parent::__construct('qa');
	}
}