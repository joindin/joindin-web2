<?php

namespace Joindin\Api\Entity\Event;

class Comment
{
    private $comment_uri;
    private $user_display_name;
    private $comment;
    private $created_date;
    private $verbose_comment_uri;
    private $event_uri;
    private $event_comments_uri;
    private $user_uri;

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment_uri
     */
    public function setCommentUri($comment_uri)
    {
        $this->comment_uri = $comment_uri;
    }

    /**
     * @return mixed
     */
    public function getCommentUri()
    {
        return $this->comment_uri;
    }

    /**
     * @param mixed $created_date
     */
    public function setCreatedDate($created_date)
    {
        $this->created_date = $created_date;
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }

    /**
     * @param mixed $event_comments_uri
     */
    public function setEventCommentsUri($event_comments_uri)
    {
        $this->event_comments_uri = $event_comments_uri;
    }

    /**
     * @return mixed
     */
    public function getEventCommentsUri()
    {
        return $this->event_comments_uri;
    }

    /**
     * @param mixed $event_uri
     */
    public function setEventUri($event_uri)
    {
        $this->event_uri = $event_uri;
    }

    /**
     * @return mixed
     */
    public function getEventUri()
    {
        return $this->event_uri;
    }

    /**
     * @param mixed $user_display_name
     */
    public function setUserDisplayName($user_display_name)
    {
        $this->user_display_name = $user_display_name;
    }

    /**
     * @return mixed
     */
    public function getUserDisplayName()
    {
        return $this->user_display_name;
    }

    /**
     * @param mixed $user_uri
     */
    public function setUserUri($user_uri)
    {
        $this->user_uri = $user_uri;
    }

    /**
     * @return mixed
     */
    public function getUserUri()
    {
        return $this->user_uri;
    }

    /**
     * @param mixed $verbose_comment_uri
     */
    public function setVerboseCommentUri($verbose_comment_uri)
    {
        $this->verbose_comment_uri = $verbose_comment_uri;
    }

    /**
     * @return mixed
     */
    public function getVerboseCommentUri()
    {
        return $this->verbose_comment_uri;
    }
}
