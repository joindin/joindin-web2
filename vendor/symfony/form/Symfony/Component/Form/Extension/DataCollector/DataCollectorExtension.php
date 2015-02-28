<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\DataCollector;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractExtension;

/**
 * Extension for collecting data of the forms on a page.
 *
 * @since  2.4
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DataCollectorExtension extends AbstractExtension
{
    /**
     * @var EventSubscriberInterface
     */
    private $dataCollector;

    public function __construct(FormDataCollectorInterface $dataCollector)
    {
        $this->dataCollector = $dataCollector;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTypeExtensions()
    {
        return array(
            new Type\DataCollectorTypeExtension($this->dataCollector),
        );
    }
}
