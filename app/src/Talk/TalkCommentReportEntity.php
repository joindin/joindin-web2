<?php
namespace Talk;

use Application\BaseCommentReportingEntity;

class TalkCommentReportEntity extends BaseCommentReportingEntity
{
    /**
     * Create new TalkCommentReportEntity
     *
     * @param \stdClass $data Model data retrieved from API
     */
    public function __construct(\stdClass $data)
    {
        parent::__construct($data);

        // should contain a comment
        if ($data->comment) {
            $this->comment = new TalkCommentEntity($data->comment);
            unset($data->comment);
        }
    }
}
