<?php
namespace Talk;

use Application\BaseCommentEntity;

class TalkCommentEntity extends BaseCommentEntity
{
    public function __construct(\stdClass $data)
    {
        parent::__construct($data);
        $this->commentUri = isset($this->data->uri) ? $this->data->uri : null;
    }

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

    public function canRateTalk($user_uri)
    {
        if ($this->data->user_uri == $user_uri) {
            return false;
        }

        return true;
    }
}
