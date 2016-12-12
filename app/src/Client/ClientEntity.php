<?php
namespace Client;

use Application\BaseEntity;
use DateTime;
use DateInterval;

class ClientEntity extends BaseEntity
{
    public function getId()
    {
        return substr($this->data->client_uri, strrpos($this->data->client_uri, '/') + 1);
    }

    public function getName()
    {
        return $this->data->application;
    }

    public function getDescription()
    {
        return $this->data->description;
    }

    public function getConsumerKey()
    {
        return $this->data->consumer_key;
    }

    public function getCreationDateTime()
    {
        return new \DateTimeImmutable($this->data->created_date);
    }

    public function getCallbackUrl()
    {
        return $this->data->callback_url;
    }

    public function hasConsumerSecret()
    {
        return isset($this->data->consumer_secret);
    }

    public function getConsumerSecret()
    {
        if (! $this->hasConsumerSecret()) {
            return '';
        }

        return $this->data->consumer_secret;
    }

    public function getApiUri()
    {
        return $this->data->client_uri;
    }
}
