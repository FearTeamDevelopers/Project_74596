<?php

namespace Admin\Controller;

use Admin\Etc\Controller;
use THCFrame\Events\Events as Event;

/**
 * 
 */
class CommentController extends Controller
{

    /**
     * Delete existing comment
     * 
     * @before _secured, _admin
     * @param int   $id     comment id
     */
    public function delete($id)
    {
        $this->_disableView();

        $comment = \App\Model\CommentModel::first(
                        array('id = ?' => (int) $id), array('id')
        );

        if (NULL === $comment) {
            echo $this->lang('NOT_FOUND');
        } else {
            if ($comment->delete()) {
                $this->getCache()->invalidate();
                Event::fire('admin.log', array('success', 'Comment id: ' . $id));
                echo 'success';
            } else {
                Event::fire('admin.log', array('fail', 'Comment id: ' . $id,
                    'Errors: ' . json_encode($comment->getErrors())));
                echo $this->lang('COMMON_FAIL');
            }
        }
    }

}
