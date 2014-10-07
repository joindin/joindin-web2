<?php

namespace Joindin\Api;

class Response
{
    protected $resource;
    protected $url = '';
    protected $userUrl = '';
    protected $meta = array();

    public function __construct($resource, $meta)
    {
        $this->setResource($resource);
        $this->setMeta($meta);
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param object|string[] $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getUserUrl()
    {
        return $this->userUrl;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param array $meta
     */
    public function setMeta($meta)
    {
        $this->url = $meta['this_page'];
        $this->userUrl = isset($meta['user_uri']) ? $meta['user_uri'] : null;

        unset($meta['this_page']);
        unset($meta['user_uri']);

        $this->meta = $meta;
    }
}
