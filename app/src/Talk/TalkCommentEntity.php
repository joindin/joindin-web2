<?php
namespace Talk;

class TalkCommentEntity
{
    private $data;

    /**
     * Create new TalkCommentEntity
     *
     * @param Object $data Model data retrieved from API
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getRating()
    {
        if (!isset($this->data->rating)) {
            return null;
        }

        return $this->data->rating;
    }

    public function getUserDisplayName()
    {
        if (!isset($this->data->user_display_name)) {
            return null;
        }

        return $this->data->user_display_name;
    }

    public function getUserEmailHash()
    {
        if (!isset($this->data->user_email_hash)) {
            return null;
        }

        return $this->data->user_email_hash;
    }

    public function getCommentDate()
    {
        if (!isset($this->data->created_date)) {
            return null;
        }

        return $this->data->created_date;
    }

    public function getComment()
    {
        if (!isset($this->data->comment)) {
            return null;
        }

        return $this->data->comment;
    }

    public function getCommentSource()
    {
        if (!isset($this->data->source)) {
            return null;
        }

        return $this->data->source;
    }
}
