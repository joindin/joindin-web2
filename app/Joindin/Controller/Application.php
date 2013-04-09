<?php
namespace Joindin\Controller;

class Application extends Base
{
    protected function defineRoutes(\Slim $app)
    {
        $app->get('/', array($this, 'index'));
        $app->get('/oauth/callback', array($this, 'oauth_callback'));
    }

    public function index()
    {
        $event_collection = new \Joindin\Model\API\Event();
        $hot_events      = $event_collection->getCollection(5, 1, 'hot');
        $upcoming_events = $event_collection->getCollection(5, 1, 'upcoming');

        array_walk($hot_events, array($this, 'add_attending_message'));
        array_walk($upcoming_events, array($this, 'add_attending_message'));

        echo $this->application->render(
            'Application/index.html.twig',
            array(
                'hot_events'      => $hot_events,
                'upcoming_events' => $upcoming_events
            )
        );
    }

    public function oauth_callback()
    {
        $_SESSION['access_token'] = $this->application->request()->params('access_token');
        $this->application->redirect('/');
    }

    protected function add_attending_message($event) {
        $message = $this->get_beginning_of_attending_message((int) $event->attendee_count);

        if ($event->attending) {
            $message .= '(including you) ';
        }

        $message .= $this->get_end_of_attending_message($event);

        $event->attending_message = $message;
    }


    protected function get_beginning_of_attending_message($attendee_count) {
        $message = $attendee_count . ' ';
        if (1 == $attendee_count) {
            $message .= 'person ';
        } else {
            $message .= 'people ';
        }

        return $message;
    }

    protected function get_end_of_attending_message($event) {
        $are = 'are';
        if (1 == $event->attendee_count) {
            $are = 'is';
        }

        if ($this->isPastEvent($event)) {
            $message = 'attended.';
        } else {
            $message = $are . ' attending.';
        }

        return $message;
    }

    protected function isPastEvent($event) {
        $endDate = \DateTime::createFromFormat(\DateTime::ISO8601, $event->end_date);
        $now = new \DateTime(null, $endDate->getTimezone());
        $now->setTime(0, 0, 0);

        return ($endDate < $now);
    }


}
