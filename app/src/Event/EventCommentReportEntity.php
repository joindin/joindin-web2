<?php
namespace Event;

class EventCommentReportEntity
{
    private $data;
    private $comment;

    /**
     * Create new EventCommentReportEntity
     *
     * @param Object $data Model data retrieved from API
     */
    public function __construct($data)
    {
        // should contain a comment
        if ($data->comment) {
            $this->comment = new EventCommentEntity($data->comment);
            unset($data->comment);
        }
        $this->data = $data;
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
