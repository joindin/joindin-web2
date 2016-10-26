<?php
namespace Event;

use Application\BaseCommentEntity;
use stdClass;

class EventCommentEntity extends BaseCommentEntity
{
    public function __construct(stdClass $data)
    {
        parent::__construct($data);
        if (isset($this->data->comment_uri)) {
            $this->commentUri = $this->data->comment_uri;
        }
    }
}
