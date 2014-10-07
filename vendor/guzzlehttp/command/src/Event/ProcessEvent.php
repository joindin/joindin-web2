<?php
namespace GuzzleHttp\Command\Event;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\ServiceClientInterface;

/**
 * Event emitted when the HTTP response of a command is being processed.
 *
 * Event listeners can inject a result onto the event to change the result of
 * the command. A request or response MAY be available in the event, but if a
 * result was injected into the command during a prepareEvent, then a request
 * or response may not be available. In this case, the process event is still
 * triggered, but has an initial result value. This allows subsequent listeners
 * to a command lifecycle to modify the result even further as needed.
 */
class ProcessEvent extends AbstractCommandEvent
{
    /** @var ResponseInterface */
    private $response;

    /**
     * @param CommandInterface       $command  Command
     * @param ServiceClientInterface $client   Client used to send the command
     * @param RequestInterface       $request  Request that was sent
     * @param ResponseInterface      $response Response that was received
     * @param mixed                  $result   Can specify the result up-front
     */
    public function __construct(
        CommandInterface $command,
        ServiceClientInterface $client,
        RequestInterface $request = null,
        ResponseInterface $response = null,
        $result = null
    ) {
        $this->command = $command;
        $this->client = $client;
        $this->request = $request;
        $this->response = $response;
        $this->result = $result;
    }

    /**
     * Get the response that was received for the request (if one is present).
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the processed result on the event.
     *
     * @param mixed $result Result to associate with the command
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}
