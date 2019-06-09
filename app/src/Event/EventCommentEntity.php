<?php
namespace JoindIn\Web\Event;

use JoindIn\Web\Application\BaseCommentEntity;

class EventCommentEntity extends BaseCommentEntity
{
    public function getCommentUri()
    {
        if (!isset($this->data->comment_uri)) {
            return null;
        }

        return $this->data->comment_uri;
    }
}
