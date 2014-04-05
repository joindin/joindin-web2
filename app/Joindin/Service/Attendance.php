<?php
namespace Joindin\Service;

/**
 * Class Attendance
 *
 * 
 *
 * @package Joindin\Service
 */
class Attendance
{
    protected $event;
    protected $apiEvent;
    protected $user;

    public function __construct(\Joindin\Model\API\Event $apiEvent, \Joindin\Model\Event $event, \Joindin\Model\User $user)
    {
        $this->apiEvent = $apiEvent;
        $this->event = $event;
        $this->user = $user;
    }

    public function confirm()
    {
        return $this->apiEvent->attend($this->event, $this->user);
    }
}
