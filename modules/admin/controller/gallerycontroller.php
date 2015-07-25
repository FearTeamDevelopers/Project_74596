<?php

namespace Admin\Controller;

use Admin\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Events\Events as Event;
use THCFrame\Filesystem\FileManager;
use THCFrame\Registry\Registry;

/**
 * 
 */
class GalleryController extends Controller
{

    /**
     * Check whether gallery unique identifier already exist or not
     * 
     * @param string $key
     * @return boolean
     */
    private function _checkUrlKey($key)
    {
        $status = \App\Model\GalleryModel::first(array('urlKey = ?' => $key));

        if (null === $status) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check whether user has access to gallery or not
     * 
     * @param \App\Model\GalleryModel $gallery
     * @return boolean
     */
    private function _checkAccess(\App\Model\GalleryModel $gallery)
    {
        if ($this->isAdmin() === true ||
                $gallery->getUserId() == $this->getUser()->getId()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get list of all gelleries
     * 
     * @before _secured, _participant
     */
    public function index()
    {
        $view = $this->getActionView();

        $galleries = \App\Model\GalleryModel::all();

        $view->set('galleries', $galleries);
    }

    /**
     * Create new gallery
     * 
     * @before _secured, _participant
     */
    public function add()
    {
        $view = $this->getActionView();

        if (RequestMethods::post('submitAddGallery')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/admin/gallery/');
            }

            $errors = array();
            $urlKey = $this->_createUrlKey(RequestMethods::post('title'));

            if (!$this->_checkUrlKey($urlKey)) {
                $errors['title'] = array($this->lang('ARTICLE_TITLE_IS_USED'));
            }

            $gallery = new \App\Model\GalleryModel(array(
                'title' => RequestMethods::post('title'),
                'userId' => $this->getUser()->getId(),
                'userAlias' => $this->getUser()->getWholeName(),
                'isPublic' => RequestMethods::post('public', 1),
                'urlKey' => $urlKey,
                'avatarPhotoId' => 0,
                'description' => RequestMethods::post('description'),
                'rank' => RequestMethods::post('rank', 1)
            ));

            if (empty($errors) && $gallery->validate()) {
                $id = $gallery->save();

                $this->getCache()->invalidate();
                Event::fire('admin.log', array('success', 'Gallery id: ' . $id));
                $view->successMessage($this->lang('CREATE_SUCCESS'));
                self::redirect('/admin/gallery/detail/' . $id);
            } else {
                Event::fire('admin.log', array('fail'));
                $view->set('gallery', $gallery)
                        ->set('submstoken', $this->_revalidateMutliSubmissionProtectionToken())
                        ->set('errors', $errors + $gallery->getErrors());
            }
        }
    }

    /**
     * Show detail of existing gallery
     * 
     * @before _secured, _participant
     * @param int   $id     gallery id
     */
    public function detail($id)
    {
        $view = $this->getActionView();

        $gallery = \App\Model\GalleryModel::fetchGalleryById((int) $id);

        if (null === $gallery) {
            $view->warningMessage($this->lang('NOT_FOUND'));
            $this->_willRenderActionView = false;
            self::redirect('/admin/gallery/');
        }

        $view->set('gallery', $gallery);
    }

    /**
     * Edit existing gallery
     * 
     * @before _secured, _participant
     * @param int   $id     gallery id
     */
    public function edit($id)
    {
        $view = $this->getActionView();

        $gallery = \App\Model\GalleryModel::fetchGalleryById((int) $id);

        if (NULL === $gallery) {
            $view->warningMessage($this->lang('NOT_FOUND'));
            $this->_willRenderActionView = false;
            self::redirect('/admin/gallery/');
        }

        if (!$this->_checkAccess($gallery)) {
            $view->warningMessage($this->lang('LOW_PERMISSIONS'));
            $this->_willRenderActionView = false;
            self::redirect('/admin/gallery/');
        }

        $view->set('gallery', $gallery);

        if (RequestMethods::post('submitEditGallery')) {
            if ($this->_checkCSRFToken() !== true) {
                self::redirect('/admin/gallery/');
            }

            $errors = array();
            $urlKey = $this->_createUrlKey(RequestMethods::post('title'));

            if ($gallery->getUrlKey() !== $urlKey && !$this->_checkUrlKey($urlKey)) {
                $errors['title'] = array($this->lang('ARTICLE_TITLE_IS_USED'));
            }

            if (null === $gallery->userId) {
                $gallery->userId = $this->getUser()->getId();
                $gallery->userAlias = $this->getUser()->getWholeName();
            }

            $gallery->title = RequestMethods::post('title');
            $gallery->isPublic = RequestMethods::post('public');
            $gallery->active = RequestMethods::post('active');
            $gallery->urlKey = $urlKey;
            $gallery->rank = RequestMethods::post('rank', 1);
            $gallery->description = RequestMethods::post('description');
            $gallery->avatarPhotoId = RequestMethods::post('avatar');

            if (empty($errors) && $gallery->validate()) {
                $gallery->save();

                $this->getCache()->invalidate();
                Event::fire('admin.log', array('success', 'Gallery id: ' . $id));
                $view->successMessage($this->lang('UPDATE_SUCCESS'));
                self::redirect('/admin/gallery/detail/' . $id);
            } else {
                Event::fire('admin.log', array('fail', 'Gallery id: ' . $id));
                $view->set('errors', $gallery->getErrors());
            }
        }
    }

    /**
     * Delete existing gallery and all photos (files and db)
     * 
     * @before _secured, _participant
     * @param int   $id     gallery id
     */
    public function delete($id)
    {
        $view = $this->getActionView();

        $gallery = \App\Model\GalleryModel::first(
                        array('id = ?' => (int) $id), array('id', 'title', 'created', 'userId')
        );

        if (NULL === $gallery) {
            $view->warningMessage($this->lang('NOT_FOUND'));
            $this->_willRenderActionView = false;
            self::redirect('/admin/gallery/');
        }

        if (!$this->_checkAccess($gallery)) {
            $view->warningMessage($this->lang('LOW_PERMISSIONS'));
            $this->_willRenderActionView = false;
            self::redirect('/admin/gallery/');
        }

        $view->set('gallery', $gallery);

        if (RequestMethods::post('submitDeleteGallery')) {
            if ($this->_checkCSRFToken() !== true) {
                self::redirect('/admin/gallery/');
            }

            $fm = new FileManager();
            $configuration = Registry::get('config');

            if (!empty($configuration->files)) {
                $pathToImages = trim($configuration->files->pathToImages, '/');
                $pathToThumbs = trim($configuration->files->pathToThumbs, '/');
            } else {
                $pathToImages = 'public/uploads/images';
                $pathToThumbs = 'public/uploads/images';
            }

            $photos = \App\Model\PhotoModel::all(array('galleryId = ?' => (int) $id), array('id'));

            if(!empty($photos)){
                $ids = array();
                foreach ($photos as $colPhoto) {
                    $ids[] = $colPhoto->getId();
                }

                \App\Model\PhotoModel::deleteAll(array('id IN ?' => $ids));

                $path = APP_PATH . '/' . $pathToImages . '/gallery/' . $gallery->getId();
                $pathThumbs = APP_PATH . '/' . $pathToThumbs . '/gallery/' . $gallery->getId();

                if ($path == $pathThumbs) {
                    $fm->remove($path);
                } else {
                    $fm->remove($path);
                    $fm->remove($pathThumbs);
                }
            }

            if ($gallery->delete()) {
                $this->getCache()->invalidate();
                Event::fire('admin.log', array('success', 'Gallery id: ' . $id));
                $view->successMessage($this->lang('DELETE_SUCCESS'));
                self::redirect('/admin/gallery/');
            } else {
                Event::fire('admin.log', array('fail', 'Gallery id: ' . $id));
                $view->warningMessage($this->lang('COMMON_FAIL'));
                self::redirect('/admin/gallery/');
            }
        }
    }

    /**
     * Return list of galleries to insert gallery link to content
     * 
     * @before _secured, _participant
     */
    public function insertToContent()
    {
        $view = $this->getActionView();
        $this->willRenderLayoutView = false;

        $galleries = \App\Model\GalleryModel::all(array(), array('urlKey', 'title'));

        $view->set('galleries', $galleries);
    }

    /**
     * Upload photo into gallery
     * 
     * @before _secured, _participant
     * @param int   $id     gallery id
     */
    public function addPhoto($id)
    {
        $view = $this->getActionView();

        $gallery = \App\Model\GalleryModel::first(
                        array(
                    'id = ?' => (int) $id,
                    'active = ?' => true
                        ), array('id', 'title', 'userId')
        );

        if (null === $gallery) {
            $view->warningMessage($this->lang('NOT_FOUND'));
            $this->_willRenderActionView = false;
            self::redirect('/admin/gallery/');
        }

        if (!$this->_checkAccess($gallery)) {
            $view->warningMessage($this->lang('LOW_PERMISSIONS'));
            $this->_willRenderActionView = false;
            self::redirect('/admin/gallery/');
        }

        $view->set('gallery', $gallery);

        if (RequestMethods::post('submitAddPhoto')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/admin/gallery/');
            }
            $errors = $uploadErrors = array();

            $fileManager = new FileManager(array(
                'thumbWidth' => $this->getConfig()->thumb_width,
                'thumbHeight' => $this->getConfig()->thumb_height,
                'thumbResizeBy' => $this->getConfig()->thumb_resizeby,
                'maxImageWidth' => $this->getConfig()->photo_maxwidth,
                'maxImageHeight' => $this->getConfig()->photo_maxheight
            ));

            $fileErrors = $fileManager->uploadImage('uploadfile', 'gallery/' . $gallery->getId(), time() . '_')->getUploadErrors();
            $files = $fileManager->getUploadedFiles();

            if (!empty($fileErrors)) {
                $uploadErrors += $fileErrors;
            }

            if (!empty($files)) {
                foreach ($files as $i => $file) {
                    if ($file instanceof \THCFrame\Filesystem\Image) {
                        $info = $file->getOriginalInfo();

                        $photo = new \App\Model\PhotoModel(array(
                            'galleryId' => $gallery->getId(),
                            'imgMain' => trim($file->getFilename(), '.'),
                            'imgThumb' => trim($file->getThumbname(), '.'),
                            'description' => RequestMethods::post('description'),
                            'rank' => RequestMethods::post('rank', 1),
                            'photoName' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                            'mime' => $info['mime'],
                            'format' => $info['format'],
                            'width' => $file->getWidth(),
                            'height' => $file->getHeight(),
                            'size' => $file->getSize()
                        ));

                        if ($photo->validate()) {
                            $aid = $photo->save();

                            Event::fire('admin.log', array('success', 'Photo id: ' . $aid . ' in gallery ' . $gallery->getId()));
                        } else {
                            Event::fire('admin.log', array('fail', 'Photo in gallery ' . $gallery->getId()));
                            $uploadErrors += $photo->getErrors();
                        }
                    }
                }
            }

            $errors['uploadfile'] = $uploadErrors;
            
            if (empty($errors['uploadfile'])) {
                $view->successMessage($this->lang('UPLOAD_SUCCESS'));
                self::redirect('/admin/gallery/detail/' . $gallery->getId());
            } else {
                $view->set('errors', $errors);
            }
        }
    }

    /**
     * Delete photo
     * 
     * @before _secured, _participant
     * @param int   $id     photo id
     */
    public function deletePhoto($id)
    {
        $this->_disableView();

        $photo = \App\Model\PhotoModel::first(
                        array('id = ?' => $id), array('id', 'imgMain', 'imgThumb', 'galleryId')
        );

        if (null === $photo) {
            echo $this->lang('NOT_FOUND');
        } else {
            $gallery = \App\Model\GalleryModel::first(
                            array('id = ?' => (int) $photo->getGalleryId()), array('id', 'userId')
            );

            if (null === $gallery) {
                echo $this->lang('NOT_FOUND');
            }

            if (!$this->_checkAccess($gallery)) {
                echo $this->lang('LOW_PERMISSIONS');
            }

            $mainPath = $photo->getUnlinkPath();
            $thumbPath = $photo->getUnlinkThumbPath();

            if ($photo->delete()) {
                @unlink($mainPath);
                @unlink($thumbPath);
                Event::fire('admin.log', array('success', 'Photo id: ' . $id));
                echo 'success';
            } else {
                Event::fire('admin.log', array('fail', 'Photo id: ' . $id));
                echo $this->lang('COMMON_FAIL');
            }
        }
    }

    /**
     * Change photo state (active/inactive)
     * 
     * @before _secured, _participant
     * @param int   $id     photo id
     */
    public function changePhotoStatus($id)
    {
        $this->_disableView();

        $photo = \App\Model\PhotoModel::first(array('id = ?' => (int) $id));

        if (null === $photo) {
            echo $this->lang('NOT_FOUND');
        } else {
            $gallery = \App\Model\GalleryModel::first(
                            array('id = ?' => (int) $photo->getGalleryId()), array('id', 'userId')
            );

            if (null === $gallery) {
                echo $this->lang('NOT_FOUND');
            }

            if (!$this->_checkAccess($gallery)) {
                echo $this->lang('LOW_PERMISSIONS');
            }

            if (!$photo->active) {
                $photo->active = true;

                if ($photo->validate()) {
                    $photo->save();
                    Event::fire('admin.log', array('success', 'Photo id: ' . $id));
                    echo 'active';
                } else {
                    echo join('<br/>', $photo->getErrors());
                }
            } elseif ($photo->active) {
                $photo->active = false;

                if ($photo->validate()) {
                    $photo->save();
                    Event::fire('admin.log', array('success', 'Photo id: ' . $id));
                    echo 'inactive';
                } else {
                    echo join('<br/>', $photo->getErrors());
                }
            }
        }
    }

}
