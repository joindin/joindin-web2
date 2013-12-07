<?php
namespace Joindin\Model\API;

class JoindIn
{
    protected $baseApiUrl = 'http://api.joind.in';
    protected $accessToken;

    public function __construct($accessToken)
    {
        $app = \Slim::getInstance();
        $config = $app->config('custom');

        if (isset($config['apiUrl'])) {
            $this->baseApiUrl = $config['apiUrl'];
        }
        $this->accessToken = $accessToken;
    }

    protected function apiGet($url, $params = array())
    {
        $paramsString = count($params) ? '?' . http_build_query($params, '', '&') : '';
        
        $contextOpts = array('http' => array(
            'header'  => "Accept: application/json",
            'timeout' => 10,
            'ignore_errors' => true,
            )
        );
        
        if ($this->accessToken) {
            $contextOpts['http']['header'] .= "\r\nAuthorization: OAuth {$this->accessToken}";
        }

        $streamContext = stream_context_create($contextOpts);
        $result = file_get_contents($url.$paramsString, 0, $streamContext);

        if (false === $result) {
            throw new \Exception('Unable to connect to API');
        }

        return $result;
    }

    protected function apiPost($url, $params = array())
    {
        $contextOpts = array('http' => array(
            'method'  => 'POST',
            'header'  => "Content-type: application/json\r\n"
                       . "Accept: application/json",
            'content' => json_encode($params),
            'timeout' => 10,
            'ignore_errors' => true,
            )
        );

        if ($this->accessToken) {
            $contextOpts['http']['header'] .= "\r\nAuthorization: OAuth {$this->accessToken}";
        }
        
        $streamContext = stream_context_create($contextOpts);
        $result = file_get_contents($url, 0, $streamContext);
        if (false === $result) {
            throw new \Exception('Unable to connect to API');
        }

        return $result;
    }
}
