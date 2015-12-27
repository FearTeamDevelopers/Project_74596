<?php

namespace Search\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class SearchIndexModel extends Model
{
    /**
     * @readwrite
     */
    protected $_alias = 'si';

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
     * @validate required, alphanumeric, max(100)
     * @label word
     */
    protected $_sword;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 350
     * 
     * @validate path, max(350)
     * @label path to source
     */
    protected $_pathToSource;

    /**
     * @column
     * @readwrite
     * @type varchar
     * @length 150
     * 
     * @validate path, max(150)
     * @label source title
     */
    protected $_sourceTitle;

    /**
     * @column
     * @readwrite
     * @type text
     * @null
     * 
     * @validate alphanumeric
     * @label source meta description
     */
    protected $_sourceMetaDescription;

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
    protected $_sourceCreated;

    /**
     * @column
     * @readwrite
     * @type smallint
     * @unsigned
     * 
     * @validate numeric, max(5)
     * @label occurence
     */
    protected $_occurence;

    /**
     * @column
     * @readwrite
     * @type smallint
     * @unsigned
     * 
     * @validate numeric, max(5)
     * @label weight
     */
    protected $_weight;

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
