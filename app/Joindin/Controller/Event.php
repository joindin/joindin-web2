<?php
namespace Joindin\Controller;


class Event extends Base
{
    protected function defineRoutes(\Slim $app)
    {
        $app->get('/event', array($this, 'index'));
        $app->get('/event/view/:id', array($this, 'show'));
        $app->get('/event/view/photos/:slug', array($this, 'callFlickrApi'));
    }

    public function index()
    {
        $event = new \Joindin\Model\API\Event();
        $events = $event->getCollection(10, 1, 'hot');
        echo $this->application->render(
            'Event/index.html.twig',
            array('events' => $events)
        );
    }

    public function show($id)
    {
        $apiEvent = new \Joindin\Model\API\Event();
        $event = $apiEvent->getBySlug($id);

        echo $this->application->render(
            'Event/show.html.twig',
            array(
                'event' => $event->getTemplateData(),
                'slug' => $id,
            )
        );
    }

    /**
     * Uses PhotoService to retrieve machine-tagged
     * photos from Flickr
     *
     * @param $slug
     */
    public function callFlickrApi($slug)
    {
        $photoService = new \Joindin\Service\PhotoService();
        $photos = $photoService->getTaggedPhotos('event', $slug);

        $app = \Slim::getInstance();
        $app->contentType('application/json');
        echo $photos;
        exit();
    }
}
