<?php
namespace Joindin\Controller;

class Event extends Base
{
    protected function defineRoutes(\Slim $app)
    {
        $app->get('/event', array($this, 'index'));
        $app->get('/event/view/:id', array($this, 'show'));

    }

    public function index()
    {
        $event = new \Joindin\Model\Collection\Event();
        $result = $event->retrieve(10, 1, 'hot');

        echo $this->application->render(
            'Event/index.html.twig',
            array('events' => $result)
        );
    }

    public function show($id)
    {
        $event = new \Joindin\Model\Event();
        $event->load($id);

        echo $this->application->render(
            'Event/show.html.twig',
            array('event' => $event)
        );
    }
}