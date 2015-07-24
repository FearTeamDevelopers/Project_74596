<?php

namespace App\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class PhotoModel extends Model
{

    /**
     * @readwrite
     */
    protected $_alias = 'ph';

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
     * @type integer
     * @index
     * 
     * @validate required, numeric, max(8)
     */
    protected $_galleryId;

    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     * 
     * @validate max(3)
     */
    protected $_active;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 256
     * 
     * @validate alphanumeric, max(1000)
     * @label popis
     */
    protected $_description;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 60
     * 
     * @validate alphanumeric, max(60)
     * @label název fotky
     */
    protected $_photoName;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 250
     * 
     * @validate required, path, max(250)
     * @label photo path
     */
    protected $_imgMain;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 250
     * 
     * @validate required, path, max(250)
     * @label thumb path
     */
    protected $_imgThumb;

    /**
     * @column
     * @readwrite
     * @type tinyint
     * 
     * @validate numeric, max(2)
     * @label rank
     */
    protected $_rank;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 32
     * 
     * @validate required, max(32)
     * @label mime type
     */
    protected $_mime;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 10
     * 
     * @validate required, alpha, max(8)
     * @label format
     */
    protected $_format;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate required, numeric, max(8)
     * @label size
     */
    protected $_size;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate required, numeric, max(8)
     * @label width
     */
    protected $_width;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate required, numeric, max(8)
     * @label height
     */
    protected $_height;

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
            $this->setActive(true);
        }
        $this->setModified(date('Y-m-d H:i:s'));
    }

    /**
     * 
     * @return type
     */
    public function getFormatedSize($unit = 'kb')
    {
        $bytes = floatval($this->_size);

        $units = array(
            'b' => 1,
            'kb' => 1024,
            'mb' => pow(1024, 2),
            'gb' => pow(1024, 3)
        );

        $result = $bytes / $units[strtolower($unit)];
        $result = strval(round($result, 2)) . ' ' . strtoupper($unit);

        return $result;
    }

    /**
     * 
     * @return type
     */
    public function getUnlinkPath($type = true)
    {
        if ($type && !empty($this->_imgMain)) {
            if (file_exists(APP_PATH . $this->_imgMain)) {
                return APP_PATH . $this->_imgMain;
            } elseif (file_exists('.' . $this->_imgMain)) {
                return '.' . $this->_imgMain;
            } elseif (file_exists('./' . $this->_imgMain)) {
                return './' . $this->_imgMain;
            }
        } else {
            return $this->_imgMain;
        }
    }

    /**
     * 
     * @return type
     */
    public function getUnlinkThumbPath($type = true)
    {
        if ($type && !empty($this->_imgThumb)) {
            if (file_exists(APP_PATH . $this->_imgThumb)) {
                return APP_PATH . $this->_imgThumb;
            } elseif (file_exists('.' . $this->_imgThumb)) {
                return '.' . $this->_imgThumb;
            } elseif (file_exists('./' . $this->_imgThumb)) {
                return './' . $this->_imgThumb;
            }
        } else {
            return $this->_imgThumb;
        }
    }

    /**
     * 
     * @param type $galleryId
     * @param type $limit
     * @param type $page
     * @return type
     */
    public static function fetchPhotosByGalleryIdPaged($galleryId, $limit = 30, $page = 1)
    {
        return self::all(
                    array('active = ?' => true, 'galleryId = ?' => (int) $galleryId), 
                    array('*'), 
                    array('rank' => 'desc', 'created' => 'desc'), 
                    $limit, $page
        );
    }

}
