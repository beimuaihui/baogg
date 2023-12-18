<?php

namespace App\Model;

class Content  extends \Baogg\Db\Table
{
    /**
     * The default table name
     */
    protected $_name;
    protected $_db;
    protected $_primary;

    /**
     * 初使化Model commission  
     *
     * @param string $db_key     数据库键值
     * @param string $table_name 表名
     * @param string $pk         请键
     */
    function __construct($db_key = 'baogg', $table_name = 'content', $pk = 'content_id')
    {
        $this->_name =   $table_name;
        $this->_primary = $pk;
        parent::__construct($db_key);
    }
}
