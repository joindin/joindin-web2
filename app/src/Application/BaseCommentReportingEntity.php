<?php
namespace JoindIn\Web\Application;

use stdClass;

abstract class BaseCommentReportingEntity extends BaseEntity
{
    protected $comment;

    public function __construct(stdClass $data)
    {
        parent::__construct($data);
        $this->comment = null;
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
