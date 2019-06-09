<?php
namespace JoindIn\Web\Event;

use JoindIn\Web\Application\BaseCommentReportingEntity;
use stdClass;

class EventCommentReportEntity extends BaseCommentReportingEntity
{
    /**
     * Create new EventCommentReportEntity
     *
     * @param stdClass $data Model data retrieved from API
     */
    public function __construct(stdClass $data)
    {
        parent::__construct($data);

        // should contain a comment
        if ($data->comment) {
            $this->comment = new EventCommentEntity($data->comment);
            unset($data->comment);
        }
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function getReportingDate()
    {
        return $this->data->reporting_date;
    }

    public function getReportingUser()
    {
        return $this->data->reporting_user_username;
    }
}
