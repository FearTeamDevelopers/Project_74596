<?php

namespace Admin\Controller;

use Admin\Etc\Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Events\Events as Event;

/**
 * 
 */
class ConceptController extends Controller
{
    /**
     * @before _secured, _participant
     */
    public function store()
    {
        $this->_disableView();

        $conceptId = RequestMethods::post('conceptid', 0);

        if ((int) $conceptId === 0) {
            $concept = new \Admin\Model\ConceptModel(array(
                'userId' => $this->getUser()->getId(),
                'type' => RequestMethods::post('type'),
                'title' => RequestMethods::post('title'),
                'shortBody' => RequestMethods::post('shorttext'),
                'body' => RequestMethods::post('text'),
                'keywords' => RequestMethods::post('keywords'),
                'metaTitle' => RequestMethods::post('metatitle'),
                'metaDescription' => RequestMethods::post('metadescription'),
            ));
            if ($concept->validate()) {
                $id = $concept->save();

                Event::fire('admin.log', array('success', 'Concept id: '.$id));
                echo $id;
            } else {
                Event::fire('admin.log', array('fail', 'Concept id: new concept'.
                    ' Errors: '.json_encode($concept->getErrors()), ));

                echo 'fail';
            }
        } else {
            $concept = \Admin\Model\ConceptModel::first(array('id = ?' => (int) $conceptId));

            $concept->title = RequestMethods::post('title');
            $concept->shortBody = RequestMethods::post('shorttext');
            $concept->body = RequestMethods::post('text');
            $concept->keywords = RequestMethods::post('keywords');
            $concept->metaTitle = RequestMethods::post('metatitle');
            $concept->metaDescription = RequestMethods::post('metadescription');

            if ($concept->validate()) {
                $concept->save();

                Event::fire('admin.log', array('success', 'Concept id: '.$concept->getId()));
                echo $concept->getId();
            } else {
                Event::fire('admin.log', array('fail', 'Concept id: '.$conceptId.
                    ' Errors: '.json_encode($concept->getErrors()), ));

                echo 'fail';
            }
        }
    }

    /**
     * @before _secured, _participant
     *
     * @param type $id
     */
    public function delete($id)
    {
        $this->_disableView();

        $concept = \Admin\Model\ConceptModel::first(array('id = ?' => (int) $id, 'userId = ?' => $this->getUser()->getId()));

        if (null === $concept) {
            echo $this->lang('NOT_FOUND');
        } else {
            if ($concept->delete()) {
                Event::fire('admin.log', array('success', 'Concept id: '.$id));
                echo 'success';
            } else {
                Event::fire('admin.log', array('fail', 'Concept id: '.$id,
                    'Errors: '.json_encode($concept->getErrors()), ));
                echo $this->lang('COMMON_FAIL');
            }
        }
    }
}
