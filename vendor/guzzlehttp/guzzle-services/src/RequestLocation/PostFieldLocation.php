<?php

namespace GuzzleHttp\Command\Guzzle\RequestLocation;

use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Post\PostBodyInterface;
use GuzzleHttp\Command\Guzzle\GuzzleCommandInterface;
use GuzzleHttp\Command\Guzzle\Operation;

/**
 * Adds POST fields to a request
 */
class PostFieldLocation extends AbstractLocation
{
    public function visit(
        GuzzleCommandInterface $command,
        RequestInterface $request,
        Parameter $param,
        array $context
    ) {
        $body = $request->getBody();
        if (!($body instanceof PostBodyInterface)) {
            throw new \RuntimeException('Must be a POST body interface');
        }

        $body->setField(
            $param->getWireName(),
            $this->prepareValue($command[$param->getName()], $param)
        );
    }

    public function after(
        GuzzleCommandInterface $command,
        RequestInterface $request,
        Operation $operation,
        array $context
    ) {
        $additional = $operation->getAdditionalParameters();
        if ($additional && $additional->getLocation() == $this->locationName) {

            $body = $request->getBody();
            if (!($body instanceof PostBodyInterface)) {
                throw new \RuntimeException('Must be a POST body interface');
            }

            foreach ($command->toArray() as $key => $value) {
                if (!$operation->hasParam($key)) {
                    $body->setField(
                        $key,
                        $this->prepareValue($value, $additional)
                    );
                }
            }
        }
    }
}
