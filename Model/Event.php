<?php
namespace Joindin\Model;

/**
 * @property string $name
 * @property string $start_date
 * @property string $end_date
 * @property string $description
 * @property string $href
 * @property string $attendee_count
 * @property string $icon
 * @property string $latitude
 * @property string $longitude
 * @property string $tz_continent
 * @property string $tz_place
 * @property string $location
 * @property string $comments_enabled
 * @property string $event_comment_count
 * @property string $cfp_start_date
 * @property string $cfp_end_date
 */
class Event extends \Joindin\Model\API\JoindIn
{
    public function load($id)
    {
        $event = current(current((array)json_decode(
            $this->apiGet(
                $this->baseApiUrl
                .'/v2.1/events/'.$id.'?format=json&verbose=yes'
            )
        )));

        $event->comments = current((array)json_decode(
            $this->apiGet($event->comments_uri))
        );

        // import properties
        foreach($event as $key => $value) {
            $this->$key = $value;
        }
    }
}
