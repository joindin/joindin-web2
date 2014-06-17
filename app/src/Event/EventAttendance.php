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
    protected $apiEvent;

    public function __construct(EventApi $apiEvent, EventEntity $event, \User\UserEntity $user)
    {
        $this->apiEvent = $apiEvent;
        $this->event = $event;
        $this->user = $user;
    }

    public function confirm(EventEntity $event, \User\UserEntity $user)
    {
        return $this->apiEvent->attend($event, $user);
    }
}
