<?php

namespace Form\Listener;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Form\Shared\UrlResolver;

/**
 * Follows a submitted URL to find the actual protocol and final address
 *
 */
class GetResolvedUrlListener implements EventSubscriberInterface
{
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        
        if ($data === '') {
            return;
        }

        if ($data === null) {
            return;
        }

        $resolver = new UrlResolver();
        try {
            $redirectURL = $resolver->resolve($data);
            $event->setData($redirectURL);
        } catch (\Exception $e) {
            // We can do nothing with this now, this will be caught by the constraint
        }
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::SUBMIT => 'onSubmit'];
    }
}
