<?php
namespace Event;

use Application\BaseCommentEntity;
use stdClass;

class EventCommentEntity extends BaseCommentEntity
{
    public function __construct(stdClass $data)
    {
        parent::__construct($data);
        $this->commentUri = isset($data->comment_uri) ? $data->comment_uri : null;
    }
}
