<?php

namespace Form\Listener;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Form\Shared\UrlResolver;

/**
 * Follows a submitted URL to find the actual protocol and final address
 */
class GetResolvedUrlListener implements EventSubscriberInterface
{
    private \Form\Shared\UrlResolver $urlResolver;

    public function __construct(UrlResolver $urlResolver = null)
    {
        $this->urlResolver = $urlResolver ?? new UrlResolver();
    }

    public function onSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        if ($data === '') {
            return;
        }

        if ($data === null) {
            return;
        }

        try {
            $redirectURL = $this->urlResolver->resolve($data);
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
