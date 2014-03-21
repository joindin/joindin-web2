<?php
namespace Application;

class BaseApi
{
    protected $baseApiUrl;
    protected $accessToken;

    public function __construct($config, $accessToken)
    {
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

        $status = 0;
        if (preg_match('@HTTP\/1\.[0|1] (\d+) @', $http_response_header[0], $matches)) {
            $status = $matches[1];
        }

        return array($status, $result);
    }
}
