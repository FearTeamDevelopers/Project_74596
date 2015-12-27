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
     * @unsigned
     */
    protected $_id;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 100
     * 
     * @validate alpha, max(100)
     * @label source model
     */
    protected $_sourceModel;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 100
     * 
     * @validate alpha, max(100)
     * @label table
     */
    protected $_idxTableAlias;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 100
     * 
     * @validate alphanumeric, max(100)
     * @label run by
     */
    protected $_runBy;

    /**
     * @column
     * @readwrite
     * @index
     * @type tinyint
     * @length 1
     * 
     * @default 0
     * @validate max(1)
     */
    protected $_isManualIndex;

    /**
     * @column
     * @readwrite
     * @type smallint
     * @unsigned
     * 
     * @validate numeric, max(8)
     * @label words count
     */
    protected $_wordsCount;

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
}
