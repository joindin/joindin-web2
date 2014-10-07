<?php

namespace Joindin\Api\Entity\Talk;

class Comment
{
    private $uri;
    private $rating;
    private $user_display_name;
    private $talk_title;
    private $comment;
    private $source;
    private $created_date;
    private $verbose_uri;
    private $talk_uri;
    private $talk_comments_uri;
    private $user_uri;

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
     * @param mixed $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return mixed
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $talk_comments_uri
     */
    public function setTalkCommentsUri($talk_comments_uri)
    {
        $this->talk_comments_uri = $talk_comments_uri;
    }

    /**
     * @return mixed
     */
    public function getTalkCommentsUri()
    {
        return $this->talk_comments_uri;
    }

    /**
     * @param mixed $talk_title
     */
    public function setTalkTitle($talk_title)
    {
        $this->talk_title = $talk_title;
    }

    /**
     * @return mixed
     */
    public function getTalkTitle()
    {
        return $this->talk_title;
    }

    /**
     * @param mixed $talk_uri
     */
    public function setTalkUri($talk_uri)
    {
        $this->talk_uri = $talk_uri;
    }

    /**
     * @return mixed
     */
    public function getTalkUri()
    {
        return $this->talk_uri;
    }

    /**
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
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
     * @param mixed $verbose_uri
     */
    public function setVerboseUri($verbose_uri)
    {
        $this->verbose_uri = $verbose_uri;
    }

    /**
     * @return mixed
     */
    public function getVerboseUri()
    {
        return $this->verbose_uri;
    }

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
}
