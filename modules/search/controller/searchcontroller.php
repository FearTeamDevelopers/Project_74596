<?php

namespace Search\Controller;

use Search\Etc\Controller;
use THCFrame\Core\StringMethods;
use THCFrame\Request\RequestMethods;

/**
 * 
 */
class SearchController extends Controller
{

    /**
     * Clean string. Cleaned string contains only [a-z0-9\s]
     * 
     * @param string $str
     * @return string
     */
    private function _cleanString($str)
    {
        $cleanStr = StringMethods::removeDiacriticalMarks($str);
        $cleanStr = strtolower(trim($cleanStr));
        $cleanStr = preg_replace('/[^a-z0-9\s]+/', ' ', $cleanStr);
        $cleanStr2 = preg_replace('/\s+/', ' ', $cleanStr);

        unset($cleanStr);
        return $cleanStr2;
    }

    /**
     * Main search method
     * 
     * @return json encoded array
     */
    public function doSearch($page = 1)
    {
        $this->_willRenderActionView = false;
        $this->_willRenderLayoutView = FALSE;

        $query = RequestMethods::post('str');

        $cleanStr = $this->_cleanString($query);
        $articlesPerPage = $this->getConfig()->search_results_per_page;

        $words = explode(' ', $cleanStr);
        $searchQuery = \Search\Model\SearchIndexModel::getQuery(
                        array('DISTINCT (si.pathToSource)', 'si.sourceTitle', 'si.sourceMetaDescription', 'si.sourceCreated'));

        foreach ($words as $key => $word) {
            if (strlen($word) < 3) {
                unset($words[$key]);
                continue;
            }

            $paramArr[] = $word;
        }

        if (count($words) > 0) {
            $whereCondArr = array_fill(0, count($words), "si.sword LIKE '%%?%%'");
            $whereCond = "si.sourceModel NOT LIKE '%%AdvertisementModel%%' AND (" . implode(' OR ', $whereCondArr) . ")";
            array_unshift($paramArr, $whereCond);
        } else {
            unset($searchQuery);
            echo json_encode(array());
        }

        if ($paramArr === null) {
            unset($searchQuery);
            echo json_encode(array());
        } else {
            call_user_func_array(array($searchQuery, 'wheresql'), $paramArr);

            $searchQuery->order('si.weight', 'DESC')
                    ->order('si.occurence', 'DESC')
                    ->limit(100);
            $searchResult = \Search\Model\SearchIndexModel::initialize($searchQuery);

            $searchReturnArr = array();
            if (null !== $searchResult) {
                $searchReturnArr['totalCount'] = count($searchResult);

                foreach ($searchResult as $model) {
                    $searchReturnArr[strval($model->getSourceTitle())] = array('path' => $model->getPathToSource(),
                        'meta' => $model->getSourceMetaDescription(),
                        'created' => $model->getSourceCreated());
                }
            }
        }

        $slicedReturnArr = array_slice($searchReturnArr, (int) $articlesPerPage * ((int) $page - 1), $articlesPerPage + 1);

        echo json_encode($slicedReturnArr);
    }

    /**
     * Main bazaar search method
     * 
     * @return json encoded array
     */
    public function doAdSearch($page = 1)
    {
        $this->_willRenderActionView = false;
        $this->_willRenderLayoutView = FALSE;

        $query = RequestMethods::post('adstr');

        $cleanStr = $this->_cleanString($query);
        $articlesPerPage = $this->getConfig()->search_results_per_page;

        $words = explode(' ', $cleanStr);
        $searchQuery = \Search\Model\SearchIndexModel::getQuery(
                        array('DISTINCT (si.pathToSource)', 'si.sourceTitle', 'si.sourceMetaDescription', 'si.sourceCreated'));

        foreach ($words as $key => $word) {
            if (strlen($word) < 3) {
                unset($words[$key]);
                continue;
            }

            $paramArr[] = $word;
        }

        if (count($words) > 0) {
            $whereCondArr = array_fill(0, count($words), "si.sword LIKE '%%?%%'");
            $whereCond = "si.sourceModel LIKE '%%AdvertisementModel%%' AND (" . implode(' OR ', $whereCondArr) . ")";
            array_unshift($paramArr, $whereCond);
        } else {
            unset($searchQuery);
            echo json_encode(array());
        }

        if ($paramArr === null) {
            unset($searchQuery);
            echo json_encode(array());
        } else {
            call_user_func_array(array($searchQuery, 'wheresql'), $paramArr);

            $searchQuery->order('si.weight', 'DESC')
                    ->order('si.occurence', 'DESC')
                    ->limit(100);
            $searchResult = \Search\Model\SearchIndexModel::initialize($searchQuery);

            $searchReturnArr = array();
            if (null !== $searchResult) {
                $searchReturnArr['totalCount'] = count($searchResult);

                foreach ($searchResult as $model) {
                    $searchReturnArr[strval($model->getSourceTitle())] = array('path' => $model->getPathToSource(),
                        'meta' => $model->getSourceMetaDescription(),
                        'created' => $model->getSourceCreated());
                }
            }
        }

        $slicedReturnArr = array_slice($searchReturnArr, (int) $articlesPerPage * ((int) $page - 1), $articlesPerPage + 1);

        echo json_encode($slicedReturnArr);
    }

}
