<?php
namespace Joindin\Model;

class Event extends \Joindin\Model\API\Event
{
    private $_event;

    /**
     * Crate new Event model
     *
     * @param Object $data Model data retrieved from API
     */
    public function __construct($data)
    {
        $this->_event = $data;
    }

    public function getName()
    {
        return $this->_event->name;
    }

    public function getUrl()
    {
        return '/event/view/'.$this->getSlug();
    }

    public function getIcon()
    {
        return $this->_event->icon;
    }

    public function getStartDate()
    {
        return $this->_event->start_date;
    }

    public function getEndDate()
    {
        return $this->_event->end_date;
    }

    public function getDescription()
    {
        return $this->_event->description;
    }

    public function getTags()
    {
        return $this->_event->tags;
    }

    public function getAttendeeCount()
    {
        return $this->_event->attendee_count;
    }

    public function getCommentsCount()
    {
        return $this->_event->event_comments_count;
    }

    public function getCommentsUri()
    {
        return $this->_event->comments_uri;
    }

    public function getUri()
    {
        return $this->_event->uri;
    }

    /**
     * Twig likes arrays, so give it one
     */
    public function getTemplateData()
    {
        return (array)$this->_event;
    }

    public function getSlug()
    {
        // Slug is set if given in URL so already is known, so return it
        if (property_exists($this->_event, 'slug')) {
            return $this->_event->slug;
        }

        // Check if the event is known in the database. If it's not, then
        // generate one
        if (!$slug = $this->_getSlugFromDatabase()) {
            $name = $this->getName();
            $alphaNumericName = preg_replace("/[^0-9a-zA-Z- ]/", "", $name);

            $slug = strtolower(str_replace(' ', '-', $alphaNumericName));

            $this->_saveSlugToDatabase($slug);
        }

        return $slug;
    }

    private function _getSlugFromDatabase()
    {
        $db = new \Joindin\Service\Db;
        $data = $db->getOneByKey('events', 'name', $this->getName());
        return $data['slug'];
    }

    private function _saveSlugToDatabase($slug)
    {
        $db = new \Joindin\Service\Db;
        $data = array(
            'name' => $this->getName(),
            'slug' => $slug,
            'uri'  => $this->getUri()
        );

        return $db->save('events', $data);
    }
}
