<?php

namespace THCFrame\Security\Model;

use THCFrame\Model\Model;
use THCFrame\Registry\Registry;

/**
 * Description of FhsBaselineModel
 *
 * @author Tomy
 */
class FhsBaselineModel extends Model
{

    /**
     * @column
     * @readwrite
     * @primary
     * @type auto_increment
     * @unsigned
     */
    protected $_id;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 100
     * 
     * @validate alphanumeric, max(100)
     * @label file hash
     */
    protected $_hash;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 250
     * 
     * @validate required, path, max(250)
     * @label path
     */
    protected $_path;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     * @label last modification
     */
    protected $_lastMod;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 20
     * 
     * @validate alphanumeric, max(20)
     * @label account
     */
    protected $_acct;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     */
    protected $_created;

    /**
     * @column
     * @readwrite
     * @type char
     * @length 19
     * @null
     * 
     * @default null
     * @validate datetime, max(19)
     */
    protected $_modified;

    /**
     * 
     */
    public function preSave()
    {
        $primary = $this->getPrimaryColumn();
        $raw = $primary['raw'];

        if (empty($this->$raw)) {
            $this->setCreated(date('Y-m-d H:i:s'));
        }

        $this->setModified(date('Y-m-d H:i:s'));
    }

    public static function fetchBasicArray($acct)
    {
        $db = Registry::get('database')->get('main');
        $result = $db->execute("SELECT path, hash, lastMod FROM tb_fhsbaseline WHERE acct=?", $acct);
        
        $returnArr = array();
        
        if(!empty($result)){
            foreach($result as $row){
                $returnArr[$row['path']] = array('hash' => $row['hash'], 'lastMod' => $row['lastMod']);
            }
        }
        unset($result);
        return $returnArr;
    }
}
