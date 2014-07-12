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

    public function __construct(EventApi $apiEvent)
    {
        $this->apiEvent = $apiEvent;
    }

    public function confirm(EventEntity $event, \User\UserEntity $user)
    {
        return $this->apiEvent->attend($event, $user);
    }
}
