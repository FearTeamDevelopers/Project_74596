<?php

namespace App\Model;

use THCFrame\Model\Model;

/**
 * 
 */
class GalleryModel extends Model
{

    /**
     * @readwrite
     */
    protected $_alias = 'gl';

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
     * 
     * @validate numeric, max(8)
     */
    protected $_userId;
    
    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate numeric, max(8)
     */
    protected $_avatarPhotoId;

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
     * @length 150
     * 
     * @validate required, alphanumeric, max(150)
     * @label název
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 200
     * @unique
     * 
     * @validate required, alphanumeric, max(200)
     * @label url key
     */
    protected $_urlKey;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 80
     * 
     * @validate alphanumeric, max(80)
     * @label alias autora
     */
    protected $_userAlias;

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
     * @length 256
     * 
     * @validate required, html
     * @label popis
     */
    protected $_description;

    /**
     * @column
     * @readwrite
     * @type boolean
     * 
     * @validate max(2)
     * @lable veřejná-soukromá
     */
    protected $_isPublic;

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
     * @readwrite
     */
    protected $_photos;

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
     * @return array
     */
    public static function fetchAll()
    {
        $query = self::getQuery(array('gl.*'))
                ->join('tb_user', 'gl.userId = us.id', 'us', 
                        array('us.firstname', 'us.lastname'));
        
        return self::initialize($query);
    }

    /**
     * Called from admin module
     * @return array
     */
    public static function fetchWithLimit($limit = 10)
    {
        $query = self::getQuery(array('gl.*'))
                ->join('tb_user', 'gl.userId = us.id', 'us', 
                        array('us.firstname', 'us.lastname'))
                ->order('gl.created', 'desc')
                ->limit((int)$limit);

        return self::initialize($query);
    }
    
    /**
     * Called from admin module
     * 
     * @param type $id
     * @return type
     */
    public static function fetchGalleryById($id)
    {
        $galleryQuery = self::getQuery(array('gl.*'))
                ->leftjoin('tb_photo', 'ph.id = gl.avatarPhotoId', 'ph', 
                        array('ph.imgMain', 'ph.imgThumb'))
                ->where('gl.id = ?', (int) $id);
        $galleryArr = self::initialize($galleryQuery);

        if (!empty($galleryArr)) {
            $gallery = array_shift($galleryArr);
            return $gallery->getAllPhotosForGallery();
        } else {
            return null;
        }
    }

    /**
     * Called from app module
     * 
     * @param type $urlkey
     * @return type
     */
    public static function fetchPublicActiveGalleryByUrlkey($urlkey)
    {
        $galleryQuery = self::getQuery(array('gl.*'))
                ->leftjoin('tb_photo', 'ph.id = gl.avatarPhotoId', 'ph', 
                        array('ph.imgMain', 'ph.imgThumb'))
                ->where('gl.urlKey = ?', $urlkey)
                ->where('gl.active = ?', true)
                ->where('gl.isPublic = ?', true);
        $galleryArr = self::initialize($galleryQuery);

        if (!empty($galleryArr)) {
            $gallery = array_shift($galleryArr);
            return $gallery;
        } else {
            return null;
        }
    }

    /**
     * Called from app module
     * 
     * @param type $year
     */
    public static function fetchPublicActiveGalleries($limit = 30, $page = 1)
    {
        $query = self::getQuery(array('gl.*'))
                ->leftjoin('tb_photo', 'ph.id = gl.avatarPhotoId', 'ph', 
                        array('ph.imgMain', 'ph.imgThumb'))
                ->where('gl.active = ?', true)
                ->where('gl.isPublic = ?', true)
                ->order('gl.rank', 'desc')
                ->order('gl.created', 'desc')
                ->limit($limit, $page);

        return self::initialize($query);
    }

    /**
     * 
     * @return \App\Model\GalleryModel
     */
    public function getAllPhotosForGallery()
    {
        $photos = \App\Model\PhotoModel::all(array('galleryId = ?' => $this->getId()));

        $this->_photos = $photos;

        return $this;
    }

    /**
     * 
     * @return \App\Model\GalleryModel
     */
    public function getActPhotosForGallery()
    {
        $photos = \App\Model\PhotoModel::all(
                array('galleryId = ?' => $this->getId(), 'active = ?' => true), 
                array('*'),
                array('rank' => 'desc', 'created' => 'desc')
        );

        $this->_photos = $photos;

        return $this;
    }
}
