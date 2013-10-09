<?php
namespace Joindin\Model\API;

class JoindIn
{
    protected $baseApiUrl = 'http://api.joind.in';
    protected $token;

    public function __construct()
    {
        $app = \Slim::getInstance();
        $config = $app->config('custom');

        if (isset($config['apiUrl'])) {
            $this->baseApiUrl = $config['apiUrl'];
        }
        
        $this->token = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null;
    }

    protected function apiGet($url, $params = array())
    {
        $paramsString = count($params) ? '?' . http_build_query($params, '', '&') : '';
        
        $contextOpts = array('http' => array(
            'header'  => "Content-type: \r\n"
                       . "Accept: application/json",
            'timeout' => 10,
            )
        );
        
        if ($this->token) {
            $contextOpts['http']['header'] .= "\r\nAuthorization: OAuth {$this->token}";
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
            )
        );

        if ($this->token) {
            $contextOpts['http']['header'] .= "\r\nAuthorization: OAuth {$this->token}";
        }
        
        $streamContext = stream_context_create($contextOpts);
        $result = file_get_contents($url, 0, $streamContext);
        if (false === $result) {
            throw new \Exception('Unable to connect to API');
        }

        return $result;
    }
}
