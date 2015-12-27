<?php

namespace Admin\Model;

use Admin\Model\Basic\BasicConceptModel;

/**
 * Concept ORM class.
 */
class ConceptModel extends BasicConceptModel
{
    const CONCEPT_TYPE_ACTION = 1;
    const CONCEPT_TYPE_NEWS = 2;
    const CONCEPT_TYPE_REPORT = 3;

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
