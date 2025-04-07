<?php
namespace Application;

abstract class BaseApi
{
    protected string $baseApiUrl;

    protected ?string $accessToken = null;

    protected ?string $proxy = null;

    public function __construct($config, ?string $accessToken)
    {
        if (isset($config['apiUrl'])) {
            $this->baseApiUrl = $config['apiUrl'];
        }

        if (isset($config['proxy']) && $config['proxy']) {
            $this->proxy = $config['proxy'];
        }

        $this->accessToken = $accessToken;
    }

    private function buildContext(string $httpMethod, string $content = null) {
        $httpContextOpts = [
            'method'        => $httpMethod,
            'header'        => ['Accept: application/json'],
            'timeout'       => 10,
            'ignore_errors' => true,
        ];

        if ($httpMethod === 'POST' || $httpMethod === 'PUT') {
            $httpContextOpts['header'][] = 'Content-type: application/json';
            if ($content === null) {
                throw new \InvalidArgumentException('Content must be provided for POST/PUT requests');
            }
            $httpContextOpts['content'] = $content;
        }

        $ip    = $_SERVER['REMOTE_ADDR'];

        // Forwarded header - see RFC 7239 (http://tools.ietf.org/html/rfc7239)
        $httpContextOpts['header'][] = "Forwarded: for=$ip";
        if ($this->accessToken) {
            $httpContextOpts['header'][] = "Authorization: OAuth " . $this->accessToken;
        }

        if ($this->proxy) {
            $httpContextOpts['proxy']           = $this->proxy;
            $httpContextOpts['request_fulluri'] = true;
        }

        return stream_context_create(['http' => $httpContextOpts]);
    }

    protected function apiGet(string $url, $params = [])
    {
        $paramsString = count($params) > 0 ? '?' . http_build_query($params, '', '&') : '';
        $result = file_get_contents($url . $paramsString, false, $this->buildContext('GET'));

        if (false === $result) {
            throw new \RuntimeException('Unable to connect to API');
        }

        return $result;
    }

    protected function apiDelete(string $url, $params = [])
    {
        $paramsString = count($params) > 0 ? '?' . http_build_query($params, '', '&') : '';

        $result = file_get_contents($url . $paramsString, false, $this->buildContext('DELETE'));

        if (false === $result) {
            throw new \RuntimeException('Unable to connect to API');
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
        $result = file_get_contents(
            $url,
            false,
            $this->buildContext('POST', json_encode($params))
        );
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
        $result = file_get_contents($url, false, $this->buildContext('PUT', json_encode($params)));
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
    private function extractListOfHeaders(array $rawHeaders): array
    {
        $headers = [];
        foreach ($rawHeaders as $rawHeader) {
            $rawHeader = explode(':', $rawHeader, 2);
            if (count($rawHeader) < 2) {
                continue;
            }

            $headers[strtolower($rawHeader[0])] = trim($rawHeader[1]);
        }

        return $headers;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }
}
