<?php

namespace Application;

abstract class BaseCommentEntity extends BaseEntity
{
    public function getUserDisplayName()
    {
        if (!isset($this->data->user_display_name)) {
            return;
        }

        return $this->data->user_display_name;
    }

    public function getGravatarHash()
    {
        if (!isset($this->data->gravatar_hash)) {
            return;
        }

        return $this->data->gravatar_hash;
    }

    public function getRating()
    {
        if (!isset($this->data->rating)) {
            return;
        }

        return $this->data->rating;
    }

    public function getUserUri()
    {
        if (!isset($this->data->user_uri)) {
            return;
        }

        return $this->data->user_uri;
    }

    public function getReportedUri()
    {
        if (!isset($this->data->reported_uri)) {
            return;
        }

        return $this->data->reported_uri;
    }

    public function getCommentDate()
    {
        if (!isset($this->data->created_date)) {
            return;
        }

        return $this->data->created_date;
    }

    public function getComment()
    {
        if (!isset($this->data->comment)) {
            return;
        }

        return $this->data->comment;
    }

    public function getCommentSource()
    {
        if (!isset($this->data->source)) {
            return;
        }

        return $this->data->source;
    }

    abstract public function getCommentUri();

    public function getCommentHash()
    {
        if (empty($this->getCommentUri())) {
            return;
        }

        $hash = md5($this->getCommentUri());

        return substr($hash, 0, 6);
    }
}
