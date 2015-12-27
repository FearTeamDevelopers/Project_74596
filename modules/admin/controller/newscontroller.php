<?php

namespace Admin\Controller;

use Admin\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Events\Events as Event;
use THCFrame\Registry\Registry;
use THCFrame\Core\StringMethods;

/**
 * 
 */
class NewsController extends Controller
{
    private $_errors = array();

    /**
     * Check whether user has access to news or not.
     * 
     * @param \App\Model\NewsModel $news
     *
     * @return bool
     */
    private function _checkAccess(\App\Model\NewsModel $news)
    {
        if ($this->isAdmin() === true ||
                $news->getUserId() == $this->getUser()->getId()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check whether news unique identifier already exist or not.
     * 
     * @param type $key
     *
     * @return bool
     */
    private function _checkUrlKey($key)
    {
        $status = \App\Model\NewsModel::first(array('urlKey = ?' => $key));

        if (null === $status) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create and return new news object.
     * 
     * @return \App\Model\NewsModel
     */
    private function _createObject()
    {
        $urlKey = $urlKeyCh = $this->_createUrlKey(RequestMethods::post('title'));

        for ($i = 1; $i <= 100; $i+=1) {
            if ($this->_checkUrlKey($urlKeyCh)) {
                break;
            } else {
                $urlKeyCh = $urlKey.'-'.$i;
            }

            if ($i == 100) {
                $this->_errors['title'] = array($this->lang('ARTICLE_UNIQUE_ID'));
                break;
            }
        }

        $shortText = str_replace(array('(!read_more_link!)', '(!read_more_title!)'), array('/novinky/r/'.$urlKey, '[Celý článek]'), RequestMethods::post('shorttext'));

        $keywords = strtolower(StringMethods::removeDiacriticalMarks(RequestMethods::post('keywords')));

        $news = new \App\Model\NewsModel(array(
            'title' => RequestMethods::post('title'),
            'userId' => $this->getUser()->getId(),
            'userAlias' => $this->getUser()->getWholeName(),
            'urlKey' => $urlKeyCh,
            'approved' => $this->getConfig()->news_autopublish,
            'archive' => 0,
            'shortBody' => $shortText,
            'body' => RequestMethods::post('text'),
            'rank' => RequestMethods::post('rank', 1),
            'keywords' => $keywords,
            'metaTitle' => RequestMethods::post('metatitle', RequestMethods::post('title')),
            'metaDescription' => strip_tags(RequestMethods::post('metadescription', $shortText)),
        ));

        return $news;
    }

    /**
     * Edit existing news object.
     * 
     * @param \App\Model\NewsModel $object
     *
     * @return \App\Model\NewsModel
     */
    private function _editObject(\App\Model\NewsModel $object)
    {
        $urlKey = $urlKeyCh = $this->_createUrlKey(RequestMethods::post('title'));

        if ($object->urlKey != $urlKey && !$this->_checkUrlKey($urlKey)) {
            for ($i = 1; $i <= 100; $i+=1) {
                if ($this->_checkUrlKey($urlKeyCh)) {
                    break;
                } else {
                    $urlKeyCh = $urlKey . '-' . $i;
                }

                if ($i == 100) {
                    $this->_errors['title'] = array($this->lang('ARTICLE_TITLE_IS_USED'));
                    break;
                }
            }
        }

        if (null === $object->userId) {
            $object->userId = $this->getUser()->getId();
            $object->userAlias = $this->getUser()->getWholeName();
        }

        $shortText = str_replace(array('(!read_more_link!)', '(!read_more_title!)'), array('/novinky/r/'.$urlKey, '[Celý článek]'), RequestMethods::post('shorttext'));

        $keywords = strtolower(StringMethods::removeDiacriticalMarks(RequestMethods::post('keywords')));

        if(!$this->isAdmin()){
            $object->approved = $this->getConfig()->news_autopublish;
        }else{
            $object->approved = RequestMethods::post('approve');
        }
        
        $object->title = RequestMethods::post('title');
        $object->urlKey = $urlKeyCh;
        $object->body = RequestMethods::post('text');
        $object->shortBody = $shortText;
        $object->rank = RequestMethods::post('rank', 1);
        $object->active = RequestMethods::post('active');
        $object->archive = RequestMethods::post('archive');
        $object->keywords = $keywords;
        $object->metaTitle = RequestMethods::post('metatitle', RequestMethods::post('title'));
        $object->metaDescription = strip_tags(RequestMethods::post('metadescription',$shortText));

        return $object;
    }

    /**
     * Check if there is object used for preview saved in session.
     * 
     * @return \App\Model\NewsModel
     */
    private function _checkForObject()
    {
        $session = Registry::get('session');
        $news = $session->get('newsPreview');
        $session->erase('newsPreview');

        return $news;
    }

    /**
     * Get list of all actions. Loaded via datatables ajax.
     * For more check load function.
     * 
     * @before _secured, _participant
     */
    public function index()
    {
    }

    /**
     * Create new news.
     * 
     * @before _secured, _participant
     */
    public function add()
    {
        $view = $this->getActionView();
        $news = $this->_checkForObject();

        $newsConcepts = \Admin\Model\ConceptModel::all(array(
                    'userId = ?' => $this->getUser()->getId(),
                    'type = ?' => \Admin\Model\ConceptModel::CONCEPT_TYPE_NEWS, ),
                array('id', 'created', 'modified'), array('created' => 'DESC'), 10);

        $view->set('news', $news)
                ->set('concepts', $newsConcepts);

        if (RequestMethods::post('submitAddNews')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/admin/news/');
            }

            $news = $this->_createObject();

            if (empty($this->_errors) && $news->validate()) {
                $id = $news->save();
                $this->getCache()->erase('news');
                \Admin\Model\ConceptModel::deleteAll(array('id = ?' => RequestMethods::post('conceptid')));

                Event::fire('admin.log', array('success', 'News id: '.$id));
                $view->successMessage($this->lang('CREATE_SUCCESS'));
                self::redirect('/admin/news/');
            } else {
                Event::fire('admin.log', array('fail',
                    'Errors: '.json_encode($this->_errors + $news->getErrors()), ));
                $view->set('errors', $this->_errors + $news->getErrors())
                        ->set('submstoken', $this->_revalidateMutliSubmissionProtectionToken())
                        ->set('news', $news)
                        ->set('conceptid', RequestMethods::post('conceptid'));
            }
        }

        if (RequestMethods::post('submitPreviewNews')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/admin/news/');
            }

            $news = $this->_createObject();

            if (empty($this->_errors) && $news->validate()) {
                $session = Registry::get('session');
                $session->set('newsPreview', $news);
                \Admin\Model\ConceptModel::deleteAll(array('id = ?' => RequestMethods::post('conceptid')));

                self::redirect('/news/preview?action=add');
            } else {
                $view->set('errors', $this->_errors + $news->getErrors())
                        ->set('submstoken', $this->_revalidateMutliSubmissionProtectionToken())
                        ->set('news', $news)
                        ->set('conceptid', RequestMethods::post('conceptid'));
            }
        }
    }

    /**
     * Edit existing news.
     * 
     * @before _secured, _participant
     *
     * @param int $id news id
     */
    public function edit($id)
    {
        $view = $this->getActionView();

        $news = $this->_checkForObject();

        if (null === $news) {
            $news = \App\Model\NewsModel::first(array('id = ?' => (int) $id));

            if (null === $news) {
                $view->warningMessage($this->lang('NOT_FOUND'));
                $this->_willRenderActionView = false;
                self::redirect('/admin/news/');
            }

            if (!$this->_checkAccess($news)) {
                $view->warningMessage($this->lang('LOW_PERMISSIONS'));
                $this->_willRenderActionView = false;
                self::redirect('/admin/news/');
            }
        }

        $newsConcepts = \Admin\Model\ConceptModel::all(array(
                    'userId = ?' => $this->getUser()->getId(),
                    'type = ?' => \Admin\Model\ConceptModel::CONCEPT_TYPE_NEWS, ),
                array('id', 'created', 'modified'), array('created' => 'DESC'), 10);

        $view->set('news', $news)
                ->set('concepts', $newsConcepts);

        if (RequestMethods::post('submitEditNews')) {
            if ($this->_checkCSRFToken() !== true) {
                self::redirect('/admin/news/');
            }

            $originalNews = clone $news;
            $news = $this->_editObject($news);

            if (empty($this->_errors) && $news->validate()) {
                $news->save();
                \Admin\Model\NewsHistoryModel::logChanges($originalNews, $news);
                $this->getCache()->erase('news');
                \Admin\Model\ConceptModel::deleteAll(array('id = ?' => RequestMethods::post('conceptid')));

                Event::fire('admin.log', array('success', 'News id: '.$id));
                $view->successMessage($this->lang('UPDATE_SUCCESS'));
                self::redirect('/admin/news/');
            } else {
                Event::fire('admin.log', array('fail', 'News id: '.$id,
                    'Errors: '.json_encode($this->_errors + $news->getErrors()), ));
                $view->set('errors', $this->_errors + $news->getErrors())
                        ->set('conceptid', RequestMethods::post('conceptid'));
            }
        }

        if (RequestMethods::post('submitPreviewNews')) {
            if ($this->_checkCSRFToken() !== true) {
                self::redirect('/admin/news/');
            }

            $action = $this->_editObject($news);

            if (empty($this->_errors) && $action->validate()) {
                $session = Registry::get('session');
                $session->set('newsPreview', $news);

                self::redirect('/news/preview?action=edit');
            } else {
                $view->set('errors', $this->_errors + $news->getErrors())
                        ->set('conceptid', RequestMethods::post('conceptid'));
            }
        }
    }

    /**
     * Delete existing news.
     * 
     * @before _secured, _participant
     *
     * @param int $id news id
     */
    public function delete($id)
    {
        $this->_disableView();

        $news = \App\Model\NewsModel::first(
                        array('id = ?' => (int) $id), array('id', 'userId')
        );

        if (null === $news) {
            echo $this->lang('NOT_FOUND');
        } else {
            if ($this->_checkAccess($news)) {
                if ($news->delete()) {
                    $this->getCache()->erase('news');
                    Event::fire('admin.log', array('success', 'News id: '.$id));
                    echo 'success';
                } else {
                    Event::fire('admin.log', array('fail', 'News id: '.$id));
                    echo $this->lang('COMMON_FAIL');
                }
            } else {
                echo $this->lang('LOW_PERMISSIONS');
            }
        }
    }

    /**
     * Approve new news.
     * 
     * @before _secured, _admin
     *
     * @param int $id news id
     */
    public function approveNews($id)
    {
        $this->_disableView();

        $news = \App\Model\NewsModel::first(array('id = ?' => (int) $id));

        if (null === $news) {
            echo $this->lang('NOT_FOUND');
        } else {
            $news->approved = 1;

            if (null === $news->userId) {
                $news->userId = $this->getUser()->getId();
                $news->userAlias = $this->getUser()->getWholeName();
            }

            if ($news->validate()) {
                $news->save();
                $this->getCache()->erase('news');

                Event::fire('admin.log', array('success', 'News id: '.$id));
                echo 'success';
            } else {
                Event::fire('admin.log', array('fail', 'News id: '.$id,
                    'Errors: '.json_encode($news->getErrors()), ));
                echo $this->lang('COMMON_FAIL');
            }
        }
    }

    /**
     * Reject new news.
     * 
     * @before _secured, _admin
     *
     * @param int $id news id
     */
    public function rejectNews($id)
    {
        $this->_disableView();

        $news = \App\Model\NewsModel::first(array('id = ?' => (int) $id));

        if (null === $news) {
            echo $this->lang('NOT_FOUND');
        } else {
            $news->approved = 2;

            if (null === $news->userId) {
                $news->userId = $this->getUser()->getId();
                $news->userAlias = $this->getUser()->getWholeName();
            }

            if ($news->validate()) {
                $news->save();

                Event::fire('admin.log', array('success', 'News id: '.$id));
                echo 'success';
            } else {
                Event::fire('admin.log', array('fail', 'News id: '.$id,
                    'Errors: '.json_encode($news->getErrors()), ));
                echo $this->lang('COMMON_FAIL');
            }
        }
    }

    /**
     * Return list of news to insert news link to content.
     * 
     * @before _secured, _participant
     */
    public function insertToContent()
    {
        $view = $this->getActionView();
        $this->willRenderLayoutView = false;

        $news = \App\Model\NewsModel::all(array(), array('urlKey', 'title'));

        $view->set('news', $news);
    }

    /**
     * Execute basic operation over multiple news.
     * 
     * @before _secured, _admin
     */
    public function massAction()
    {
        $this->_disableView();

        $errors = array();

        $ids = RequestMethods::post('ids');
        $action = RequestMethods::post('action');

        if (empty($ids)) {
            echo $this->lang('NO_ROW_SELECTED');

            return;
        }

        switch ($action) {
            case 'delete':
                $news = \App\Model\NewsModel::all(array(
                            'id IN ?' => $ids,
                ));
                if (null !== $news) {
                    foreach ($news as $_news) {
                        if (!$_news->delete()) {
                            $errors[] = $this->lang('DELETE_FAIL').' - '.$_news->getTitle();
                        }
                    }
                }

                if (empty($errors)) {
                    $this->getCache()->erase('news');
                    Event::fire('admin.log', array('delete success', 'News ids: '.implode(',', $ids)));
                    echo $this->lang('DELETE_SUCCESS');
                } else {
                    Event::fire('admin.log', array('delete fail', 'Errors:'.json_encode($errors)));
                    $message = implode(PHP_EOL, $errors);
                    echo $message;
                }

                break;
            case 'activate':
                $news = \App\Model\NewsModel::all(array(
                            'id IN ?' => $ids,
                            'active = ?' => false,
                ));
                if (null !== $news) {
                    foreach ($news as $_news) {
                        $_news->active = true;

                        if (null === $_news->userId) {
                            $_news->userId = $this->getUser()->getId();
                            $_news->userAlias = $this->getUser()->getWholeName();
                        }

                        if ($_news->validate()) {
                            $_news->save();
                        } else {
                            $errors[] = "News id {$_news->getId()} - {$_news->getTitle()} errors: "
                                    .implode(', ', $_news->getErrors());
                        }
                    }
                }

                if (empty($errors)) {
                    $this->getCache()->erase('news');
                    Event::fire('admin.log', array('activate success', 'News ids: '.implode(',', $ids)));
                    echo $this->lang('ACTIVATE_SUCCESS');
                } else {
                    Event::fire('admin.log', array('activate fail', 'Errors:'.json_encode($errors)));
                    $message = implode(PHP_EOL, $errors);
                    echo $message;
                }

                break;
            case 'deactivate':
                $news = \App\Model\NewsModel::all(array(
                            'id IN ?' => $ids,
                            'active = ?' => true,
                ));
                if (null !== $news) {
                    foreach ($news as $_news) {
                        $_news->active = false;

                        if (null === $_news->userId) {
                            $_news->userId = $this->getUser()->getId();
                            $_news->userAlias = $this->getUser()->getWholeName();
                        }

                        if ($_news->validate()) {
                            $_news->save();
                        } else {
                            $errors[] = "News id {$_news->getId()} - {$_news->getTitle()} errors: "
                                    .implode(', ', $_news->getErrors());
                        }
                    }
                }

                if (empty($errors)) {
                    $this->getCache()->erase('news');
                    Event::fire('admin.log', array('deactivate success', 'News ids: '.implode(',', $ids)));
                    echo $this->lang('DEACTIVATE_SUCCESS');
                } else {
                    Event::fire('admin.log', array('deactivate fail', 'Errors:'.json_encode($errors)));
                    $message = implode(PHP_EOL, $errors);
                    echo $message;
                }

                break;
            case 'approve':
                $news = \App\Model\NewsModel::all(array(
                            'id IN ?' => $ids,
                            'approved IN ?' => array(0, 2),
                ));

                if (null !== $news) {
                    foreach ($news as $_news) {
                        $_news->approved = 1;

                        if (null === $_news->userId) {
                            $_news->userId = $this->getUser()->getId();
                            $_news->userAlias = $this->getUser()->getWholeName();
                        }

                        if ($_news->validate()) {
                            $_news->save();
                        } else {
                            $errors[] = "Action id {$_news->getId()} - {$_news->getTitle()} errors: "
                                    .implode(', ', $_news->getErrors());
                        }
                    }
                }

                if (empty($errors)) {
                    $this->getCache()->erase('news');
                    Event::fire('admin.log', array('approve success', 'Action ids: '.implode(',', $ids)));
                    echo $this->lang('UPDATE_SUCCESS');
                } else {
                    Event::fire('admin.log', array('approve fail', 'Errors:'.json_encode($errors)));
                    $message = implode(PHP_EOL, $errors);
                    echo $message;
                }

                break;
            case 'reject':
                $news = \App\Model\NewsModel::all(array(
                            'id IN ?' => $ids,
                            'approved IN ?' => array(0, 1),
                ));

                if (null !== $news) {
                    foreach ($news as $_news) {
                        $_news->approved = 2;

                        if (null === $_news->userId) {
                            $_news->userId = $this->getUser()->getId();
                            $_news->userAlias = $this->getUser()->getWholeName();
                        }

                        if ($_news->validate()) {
                            $_news->save();
                        } else {
                            $errors[] = "Action id {$_news->getId()} - {$_news->getTitle()} errors: "
                                    .implode(', ', $_news->getErrors());
                        }
                    }
                }

                if (empty($errors)) {
                    $this->getCache()->erase('news');
                    Event::fire('admin.log', array('reject success', 'Action ids: '.implode(',', $ids)));
                    echo $this->lang('UPDATE_SUCCESS');
                } else {
                    Event::fire('admin.log', array('reject fail', 'Errors:'.json_encode($errors)));
                    $message = implode(PHP_EOL, $errors);
                    echo $message;
                }

                break;
            default:
                echo $this->lang('NOT_FOUND');
                break;
        }
    }

    /**
     * Response for ajax call from datatables plugin.
     * 
     * @before _secured, _participant
     */
    public function load()
    {
        $this->_disableView();

        $page = (int) RequestMethods::post('page', 0);
        $search = RequestMethods::issetpost('sSearch') ? RequestMethods::post('sSearch') : '';

        if ($search != '') {
            $whereCond = "nw.created LIKE '%%?%%' OR nw.userAlias LIKE '%%?%%' OR nw.title LIKE '%%?%%'";

            $query = \App\Model\NewsModel::getQuery(
                            array('nw.id', 'nw.userId', 'nw.userAlias', 'nw.title',
                                'nw.active', 'nw.approved', 'nw.archive', 'nw.created', ))
                    ->join('tb_user', 'nw.userId = us.id', 'us', array('us.firstname', 'us.lastname'))
                    ->wheresql($whereCond, $search, $search, $search);

            if (RequestMethods::issetpost('iSortCol_0')) {
                $dir = RequestMethods::issetpost('sSortDir_0') ? RequestMethods::post('sSortDir_0') : 'asc';
                $column = RequestMethods::post('iSortCol_0');

                if ($column == 0) {
                    $query->order('nw.id', $dir);
                } elseif ($column == 1) {
                    $query->order('nw.title', $dir);
                } elseif ($column == 2) {
                    $query->order('nw.userAlias', $dir);
                } elseif ($column == 3) {
                    $query->order('nw.created', $dir);
                }
            } else {
                $query->order('nw.id', 'desc');
            }

            $limit = (int) RequestMethods::post('iDisplayLength');
            $query->limit($limit, $page + 1);
            $news = \App\Model\NewsModel::initialize($query);

            $countQuery = \App\Model\NewsModel::getQuery(array('nw.id'))
                    ->join('tb_user', 'nw.userId = us.id', 'us', array('us.firstname', 'us.lastname'))
                    ->wheresql($whereCond, $search, $search, $search);

            $newsCount = \App\Model\NewsModel::initialize($countQuery);
            unset($countQuery);
            $count = count($newsCount);
            unset($newsCount);
        } else {
            $query = \App\Model\NewsModel::getQuery(
                            array('nw.id', 'nw.userId', 'nw.userAlias', 'nw.title',
                                'nw.active', 'nw.approved', 'nw.archive', 'nw.created', ))
                    ->join('tb_user', 'nw.userId = us.id', 'us', array('us.firstname', 'us.lastname'));

            if (RequestMethods::issetpost('iSortCol_0')) {
                $dir = RequestMethods::issetpost('sSortDir_0') ? RequestMethods::post('sSortDir_0') : 'asc';
                $column = RequestMethods::post('iSortCol_0');

                if ($column == 0) {
                    $query->order('nw.id', $dir);
                } elseif ($column == 1) {
                    $query->order('nw.title', $dir);
                } elseif ($column == 2) {
                    $query->order('nw.userAlias', $dir);
                } elseif ($column == 3) {
                    $query->order('nw.created', $dir);
                }
            } else {
                $query->order('nw.id', 'desc');
            }

            $limit = (int) RequestMethods::post('iDisplayLength');
            $query->limit($limit, $page + 1);
            $news = \App\Model\NewsModel::initialize($query);

            $count = \App\Model\NewsModel::count();
        }

        $draw = $page + 1 + time();

        $str = '{ "draw": '.$draw.', "recordsTotal": '.$count.', "recordsFiltered": '.$count.', "data": [';

        $returnArr = array();
        if (null !== $news) {
            foreach ($news as $_news) {
                $label = '';
                if ($_news->active) {
                    $label .= "<span class='infoLabel infoLabelGreen'>Aktivní</span>";
                } else {
                    $label .= "<span class='infoLabel infoLabelRed'>Neaktivní</span>";
                }

                if ($_news->approved == \App\Model\NewsModel::STATE_APPROVED) {
                    $label .= "<span class='infoLabel infoLabelGreen'>Schváleno</span>";
                } elseif ($_news->approved == \App\Model\NewsModel::STATE_REJECTED) {
                    $label .= "<span class='infoLabel infoLabelRed'>Zamítnuto</span>";
                } else {
                    $label .= "<span class='infoLabel infoLabelOrange'>Čeká na schválení</span>";
                }

                if ($this->getUser()->getId() == $_news->getUserId()) {
                    $label .= "<span class='infoLabel infoLabelGray'>Moje</span>";
                }

                if ($_news->archive) {
                    $archiveLabel = "<span class='infoLabel infoLabelGreen'>Ano</span>";
                } else {
                    $archiveLabel = "<span class='infoLabel infoLabelGray'>Ne</span>";
                }

                $arr = array();
                $arr [] = '[ "'.$_news->getId().'"';
                $arr [] = '"'.htmlentities($_news->getTitle()).'"';
                $arr [] = '"'.$_news->getUserAlias().'"';
                $arr [] = '"'.$_news->getCreated().'"';
                $arr [] = '"'.$label.'"';
                $arr [] = '"'.$archiveLabel.'"';

                $tempStr = '"';
                if ($this->isAdmin() || $_news->userId == $this->getUser()->getId()) {
                    $tempStr .= "<a href='/admin/news/edit/".$_news->id."' class='btn btn3 btn_pencil' title='Upravit'></a>";
                    $tempStr .= "<a href='/admin/news/delete/".$_news->id."' class='btn btn3 btn_trash ajaxDelete' title='Smazat'></a>";
                }

                if ($this->isAdmin() && $_news->approved == 0) {
                    $tempStr .= "<a href='/admin/news/approvenews/".$_news->id."' class='btn btn3 btn_info ajaxReload' title='Schválit'></a>";
                    $tempStr .= "<a href='/admin/news/rejectnews/".$_news->id."' class='btn btn3 btn_stop ajaxReload' title='Zamítnout'></a>";
                }

                $arr [] = $tempStr.'"]';
                $returnArr[] = implode(',', $arr);
            }

            $str .= implode(',', $returnArr).']}';

            echo $str;
        } else {
            $str .= '[ "","","","","","",""]]}';

            echo $str;
        }
    }

    /**
     * Show help for news section.
     * 
     * @before _secured, _participant
     */
    public function help()
    {
    }

    /**
     * Load concept into active form.
     * 
     * @before _secured, _participant
     */
    public function loadConcept($id)
    {
        $this->_disableView();
        $concept = \Admin\Model\ConceptModel::first(array('id = ?' => (int) $id, 'userId = ?' => $this->getUser()->getId()));

        if (null !== $concept) {
            $conceptArr = array(
                'conceptid' => $concept->getId(),
                'title' => $concept->getTitle(),
                'shortbody' => $concept->getShortBody(),
                'body' => $concept->getBody(),
                'keywords' => $concept->getKeywords(),
                'metatitle' => $concept->getMetaTitle(),
                'metadescription' => $concept->getMetaDescription(),
            );

            echo json_encode($conceptArr);
            exit;
        } else {
            echo 'notfound';
            exit;
        }
    }
}
