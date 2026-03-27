<?php
namespace Apikey;

use Application\BaseEntity;
use DateTime;
use DateInterval;

class ApikeyEntity extends BaseEntity
{
    public function getId(): string
    {
        return substr($this->data->token_uri, strrpos($this->data->token_uri, '/') + 1);
    }

    public function getApplicationName()
    {
        return $this->data->application;
    }

    public function getLastUsedDateTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->data->last_used_date);
    }

    public function getCreationDateTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->data->created_date);
    }


    public function getApiUri()
    {
        return $this->data->token_uri;
    }
}
