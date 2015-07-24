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
     * @validate required, alphanumeric, max(100)
     * @label word
     */
    protected $_sword;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 350
     * 
     * @validate path, max(350)
     * @label path to source
     */
    protected $_pathToSource;

    /**
     * @column
     * @readwrite
     * @type text
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
     * @length 256
     * 
     * @validate alphanumeric
     * @label source meta description
     */
    protected $_sourceMetaDescription;
    
    /**
     * @column
     * @readwrite
     * @type datetime
     * 
     * @validate datetime, max(22)
     */
    protected $_sourceCreated;
    
    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate numeric, max(8)
     * @label occurence
     */
    protected $_occurence;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate numeric, max(8)
     * @label weight
     */
    protected $_weight;
    
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
