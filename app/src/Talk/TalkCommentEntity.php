<?php
namespace Talk;

use Application\BaseCommentEntity;
use stdClass;

class TalkCommentEntity extends BaseCommentEntity
{
    public function getUsername()
    {
        if (!isset($this->data->username)) {
            return null;
        }

        return $this->data->username;
    }

    public function getTalkTitle()
    {
        if (!isset($this->data->talk_title)) {
            return null;
        }

        return $this->data->talk_title;
    }

    public function getTalkUri()
    {
        if (!isset($this->data->talk_uri)) {
            return null;
        }

        return $this->data->talk_uri;
    }

    public function getCommentUri()
    {
        if (!isset($this->data->uri)) {
            return null;
        }

        return $this->data->uri;
    }

    public function canRateTalk($user_uri)
    {

        if (isset($this->data->user_uri) && $this->data->user_uri == $user_uri) {
            return false;
        }

        return true;
    }
}
