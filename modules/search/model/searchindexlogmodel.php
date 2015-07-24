<?php

namespace Search\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class SearchIndexLogModel extends Model
{

    /**
     * @readwrite
     */
    protected $_alias = 'sil';

    /**
     * @read
     */
    protected $_databaseIdent = 'search';
    
    /**
     * @column
     * @readwrite
     * @primary
     * @type auto_increment
     */
    protected $_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate alpha, max(100)
     * @label source model
     */
    protected $_sourceModel;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate alpha, max(100)
     * @label table
     */
    protected $_idxTableAlias;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate alphanumeric, max(100)
     * @label run by
     */
    protected $_runBy;
    
    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     * 
     * @validate max(3)
     */
    protected $_isManualIndex;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate numeric, max(8)
     * @label words count
     */
    protected $_wordsCount;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 22
     * 
     * @validate datetime, max(22)
     */
    protected $_created;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 22
     * 
     * @validate datetime, max(22)
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

}
