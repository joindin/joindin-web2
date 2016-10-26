<?php
namespace Application;

abstract class BaseCommentReportingEntity extends BaseEntity
{
    protected $comment;

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
