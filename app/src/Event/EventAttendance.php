<?php
namespace Event;

/**
 * Class Attendance
 *
 * 
 *
 * @package Joindin\Service
 */
class EventAttendance
{
    protected $event;
    protected $apiEvent;
    protected $user;

    public function __construct(EventApi $apiEvent, EventEntity $event, \User\UserEntity $user)
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
