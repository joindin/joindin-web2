<?php

namespace Event;

use Application\BaseCommentEntity;

class EventCommentEntity extends BaseCommentEntity
{
    public function getCommentUri()
    {
        if (!isset($this->data->comment_uri)) {
            return;
        }

        return $this->data->comment_uri;
    }
}
