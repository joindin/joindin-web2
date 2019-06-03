<?php
namespace JoindIn\Web\Apikey;

use DateTimeImmutable;
use JoindIn\Web\Application\BaseEntity;

class ApikeyEntity extends BaseEntity
{
    public function getId()
    {
        return substr($this->data->token_uri, strrpos($this->data->token_uri, '/') + 1);
    }

    public function getApplicationName()
    {
        return $this->data->application;
    }

    public function getLastUsedDateTime()
    {
        return new DateTimeImmutable($this->data->last_used_date);
    }

    public function getCreationDateTime()
    {
        return new DateTimeImmutable($this->data->created_date);
    }


    public function getApiUri()
    {
        return $this->data->token_uri;
    }
}
