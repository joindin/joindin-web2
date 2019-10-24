<?php
namespace Application;

abstract class BaseApi
{
    protected $baseApiUrl;
    protected $accessToken;
    protected $proxy;

    public function __construct($config, $accessToken)
    {
        if (isset($config['apiUrl'])) {
            $this->baseApiUrl = $config['apiUrl'];
        }

        if (isset($config['proxy']) && $config['proxy']) {
            $this->proxy = $config['proxy'];
        }

        $this->accessToken = $accessToken;
    }

    protected function apiGet($url, $params = [])
    {
        $paramsString = count($params) ? '?' . http_build_query($params, '', '&') : '';

        $contextOpts = ['http' => [
            'header'        => "Accept: application/json",
            'timeout'       => 10,
            'ignore_errors' => true,
        ]];

        // Forwarded header - see RFC 7239 (http://tools.ietf.org/html/rfc7239)
        $ip    = $_SERVER['REMOTE_ADDR'];
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
        $contextOpts['http']['header'] .= "\r\nForwarded: for=$ip;user-agent=\"$agent\"";

        if ($this->accessToken) {
            $contextOpts['http']['header'] .= "\r\nAuthorization: OAuth {$this->accessToken}";
        }

        if ($this->proxy) {
            $contextOpts['http']['proxy']           = $this->proxy;
            $contextOpts['http']['request_fulluri'] = true;
        }

        $streamContext = stream_context_create($contextOpts);
        $result        = file_get_contents($url . $paramsString, false, $streamContext);

        if (false === $result) {
            throw new \Exception('Unable to connect to API');
        }

        return $result;
    }

    protected function apiDelete($url, $params = [])
    {
        $paramsString = count($params) ? '?' . http_build_query($params, '', '&') : '';

        $contextOpts = ['http' => [
            'method'        => 'DELETE',
            'header'        => "Accept: application/json",
            'timeout'       => 10,
            'ignore_errors' => true,
        ]];

        // Forwarded header - see RFC 7239 (http://tools.ietf.org/html/rfc7239)
        $ip    = $_SERVER['REMOTE_ADDR'];
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
        $contextOpts['http']['header'] .= "\r\nForwarded: for=$ip;user-agent=\"$agent\"";

        if ($this->accessToken) {
            $contextOpts['http']['header'] .= "\r\nAuthorization: OAuth {$this->accessToken}";
        }

        if ($this->proxy) {
            $contextOpts['http']['proxy']           = $this->proxy;
            $contextOpts['http']['request_fulluri'] = true;
        }

        $streamContext = stream_context_create($contextOpts);
        $result        = file_get_contents($url . $paramsString, false, $streamContext);

        if (false === $result) {
            throw new \Exception('Unable to connect to API');
        }

        $status = 0;
        if (preg_match('@HTTP\/1\.[0|1] (\d+) @', $http_response_header[0], $matches)) {
            $status = $matches[1];
        }

        $headers = $this->extractListOfHeaders($http_response_header);

        return [$status, $result, $headers];
    }

    protected function apiPost($url, $params = [])
    {
        $contextOpts = ['http' => [
            'method' => 'POST',
            'header' => "Content-type: application/json\r\n"
                      . "Accept: application/json",
            'content'       => json_encode($params),
            'timeout'       => 10,
            'ignore_errors' => true,
        ]];

        // Forwarded header - see RFC 7239 (http://tools.ietf.org/html/rfc7239)
        $ip    = $_SERVER['REMOTE_ADDR'];
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
        $contextOpts['http']['header'] .= "\r\nForwarded: for=$ip;user-agent=\"$agent\"";

        if ($this->accessToken) {
            $contextOpts['http']['header'] .= "\r\nAuthorization: OAuth {$this->accessToken}";
        }

        if ($this->proxy) {
            $contextOpts['http']['proxy']           = $this->proxy;
            $contextOpts['http']['request_fulluri'] = true;
        }

        $streamContext = stream_context_create($contextOpts);
        $result        = file_get_contents($url, false, $streamContext);
        if (false === $result) {
            throw new \Exception('Unable to connect to API');
        }

        $status = 0;
        if (preg_match('@HTTP\/1\.[0|1] (\d+) @', $http_response_header[0], $matches)) {
            $status = $matches[1];
        }

        $headers = $this->extractListOfHeaders($http_response_header);

        return [$status, $result, $headers];
    }

    protected function apiPut($url, $params = [])
    {
        $contextOpts = ['http' => [
            'method' => 'PUT',
            'header' => "Content-type: application/json\r\n"
                . "Accept: application/json",
            'content'       => json_encode($params),
            'timeout'       => 10,
            'ignore_errors' => true,
        ]];

        // Forwarded header - see RFC 7239 (http://tools.ietf.org/html/rfc7239)
        $ip    = $_SERVER['REMOTE_ADDR'];
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
        $contextOpts['http']['header'] .= "\r\nForwarded: for=$ip;user-agent=\"$agent\"";

        if ($this->accessToken) {
            $contextOpts['http']['header'] .= "\r\nAuthorization: OAuth {$this->accessToken}";
        }

        if ($this->proxy) {
            $contextOpts['http']['proxy']           = $this->proxy;
            $contextOpts['http']['request_fulluri'] = true;
        }

        $streamContext = stream_context_create($contextOpts);
        $result        = file_get_contents($url, false, $streamContext);
        if (false === $result) {
            throw new \Exception('Unable to connect to API');
        }

        $status = 0;
        if (preg_match('@HTTP\/1\.[0|1] (\d+) @', $http_response_header[0], $matches)) {
            $status = $matches[1];
        }

        $headers = $this->extractListOfHeaders($http_response_header);

        return [$status, $result, $headers];
    }

    /**
     * Converts an array of headers, including tag, to an associative array.
     *
     * By default many header-providing methods return an array with the complete line of a header. Because we want
     * to be able to locate and return the contents of a specific header we convert the aforementioned array into an
     * associative array where the key represents the header tag, in lowercase, and the value the contents.
     *
     * @param string[] $rawHeaders
     *
     * @return string[]
     */
    private function extractListOfHeaders(array $rawHeaders)
    {
        $headers = [];
        foreach ($rawHeaders as $header) {
            $header = explode(':', $header, 2);
            if (count($header) < 2) {
                continue;
            }

            $headers[strtolower($header[0])] = trim($header[1]);
        }

        return $headers;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }
}
