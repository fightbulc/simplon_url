<?php

declare(strict_types=1);

namespace Simplon\Url;

use function array_pop;
use function array_replace_recursive;
use function array_slice;
use function count;
use function explode;
use function http_build_query;
use function implode;
use function ksort;
use function parse_str;
use function parse_url;
use function rtrim;
use function str_replace;
use function strpos;
use function trim;

class Url
{
    /** @var callable */
    private static $protocolFetch;
    /** @var string|null */
    private $url;
    /** @var mixed[]|null */
    private $elements;

    public static function setFindProtocolCallback(callable $callback) : void
    {
        self::$protocolFetch = $callback;
    }

    private static function findCurrentProtocol() : string
    {
        $callback = self::$protocolFetch;

        if (!$callback instanceof \Closure) {
            $callback = static function () {
                return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443 ? 'https://' : 'http://';
            };
        }

        return $callback();
    }

    public static function getCurrentUrl() : string
    {
        return self::findCurrentProtocol() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public function __construct(?string $url = null)
    {
        if (!$url) {
            return;
        }

        $this->url = $url;
        $this->parse();
    }

    public function getProtocol() : ?string
    {
        if ($scheme = $this->getParsedElements('scheme')) {
            return $scheme;
        }

        return null;
    }

    public function getHost() : ?string
    {
        return $this->getParsedElements('host');
    }

    public function getSubDomain() : ?string
    {
        $parts = $this->getHostParts();

        if ($parts) {
            array_pop($parts);
            array_pop($parts);

            return implode('.', $parts);
        }

        return null;
    }

    public function getDomain() : ?string
    {
        $parts = $this->getHostParts();

        if ($parts) {
            return array_slice($parts, -2, 1)[0];
        }

        return null;
    }

    public function getTopLevelDomain() : ?string
    {
        $parts = $this->getHostParts();

        if ($parts) {
            return array_slice($parts, -1, 1)[0];
        }

        return null;
    }

    public function getPort() : ?string
    {
        return $this->getParsedElements('port');
    }

    public function getUser() : ?string
    {
        return $this->getParsedElements('user');
    }

    public function getPass() : ?string
    {
        return $this->getParsedElements('pass');
    }

    public function getPath() : string
    {
        $path = null;

        if ($path = $this->getParsedElements('path')) {
            $path = trim($this->getParsedElements('path'), '/');
        }

        return '/' . $path;
    }

    public function getPathSegment(int $segment) : ?string
    {
        if ($path = $this->getParsedElements('path')) {
            $pathSegments       = explode('/', trim($path, '/'));
            $pathSegementsCount = count($pathSegments);

            if ($segment <= 0) {
                $segment = 1;
            }

            if ($segment <= $pathSegementsCount && !empty($pathSegments[$segment - 1])) {
                return $pathSegments[$segment - 1];
            }
        }

        return null;
    }

    /**
     * @return mixed[]|null
     */
    public function getAllQueryParams() : ?array
    {
        if ($query = $this->getParsedElements('query')) {
            parse_str($query, $params);

            return $params;
        }

        return null;
    }

    public function getQueryParam(string $key) : ?string
    {
        if (($params = $this->getAllQueryParams()) && isset($params[$key])) {
            return $params[$key];
        }

        return null;
    }

    public function getFragment() : ?string
    {
        return $this->getParsedElements('fragment');
    }

    public function toString() : string
    {
        $url = [];

        if ($this->getUser() && $this->getPass()) {
            $url[] = $this->getUser() . ':' . $this->getPass() . '@';
        }

        if ($host = $this->getHost()) {
            if ($this->getProtocol()) {
                $url[] = $this->getProtocol() . ':';
            }

            $url[] = '//' . $this->getHost();
        }

        if ($port = $this->getPort()) {
            $url[] = ':' . $port;
        }

        if ($path = $this->getPath()) {
            $path = trim($path, '/');

            if ($this->getHost() === null || $path) {
                $url[] = '/' . $path;
            }
        }

        if ($params = $this->getAllQueryParams()) {
            ksort($params);
            $url[] = '?' . http_build_query($params);
        }

        if ($fragment = $this->getFragment()) {
            $url[] = '#' . $fragment;
        }

        return implode('', $url);
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    /**
     * @return Url
     */
    public function withProtocol(string $value) : self
    {
        return $this->setElement('scheme', $value);
    }

    /**
     * @return Url
     */
    public function withHost(string $value) : self
    {
        if (strpos($value, '://')) {
            [$protocol, $value] = explode('://', $value);
            $this->withProtocol($protocol);
        }

        return $this->setElement('host', $value);
    }

    /**
     * @return Url
     */
    public function withSubDomain(string $value) : self
    {
        if ($host = $this->getHost()) {
            $newHost = $value . '.' . $host;

            if ($subDomain = $this->getSubDomain()) {
                $newHost = str_replace($subDomain, $value, $host);
            }

            return $this->setElement('host', $newHost);
        }

        return $this;
    }

    /**
     * @return Url
     */
    public function withDomain(string $value) : self
    {
        $host   = $this->getHost();
        $domain = $this->getDomain();

        if ($host && $domain) {
            $host = str_replace($domain, $value, $host);

            return $this->setElement('host', $host);
        }

        return $this;
    }

    /**
     * @return Url
     */
    public function withTopLevelDomain(string $value) : self
    {
        $tdl  = $this->getTopLevelDomain();
        $host = $this->getHost();

        if ($tdl && $host) {
            $host = str_replace($tdl, $value, $host);

            return $this->setElement('host', $host);
        }

        return $this;
    }

    /**
     * @return Url
     */
    public function withPort(string $value) : self
    {
        return $this->setElement('port', $value);
    }

    /**
     * @return Url
     */
    public function withUser(string $value) : self
    {
        return $this->setElement('user', $value);
    }

    /**
     * @return Url
     */
    public function withPass(string $value) : self
    {
        return $this->setElement('pass', $value);
    }

    /**
     * @param mixed[]|null $params
     *
     * @return Url
     */
    public function withPath(string $value, ?array $params = null) : self
    {
        $path = rtrim($value, '/');

        if ($params) {
            $path = $this->replacePlaceholders($path, $params);
        }

        return $this->setElement('path', $path);
    }

    /**
     * @param mixed[]|null $params
     *
     * @return Url
     */
    public function withPrefixPath(string $value, ?array $params = null) : self
    {
        $path = rtrim($value, '/') . '/' . trim($this->getPath(), '/');

        if ($params) {
            $path = $this->replacePlaceholders($path, $params);
        }

        return $this->setElement('path', $path);
    }

    /**
     * @param mixed[]|null $params
     *
     * @return Url
     */
    public function withTrailPath(string $value, ?array $params = null) : self
    {
        $path = rtrim($this->getPath(), '/') . '/' . trim($value, '/');

        if ($params) {
            $path = $this->replacePlaceholders($path, $params);
        }

        return $this->setElement('path', $path);
    }

    /**
     * @return Url
     */
    public function withPathSegment(int $segment, string $value) : self
    {
        // fallback
        $pathSegments = [$value];

        if ($path = $this->getPath()) {
            $pathSegments       = explode('/', rtrim($this->getPath(), '/'));
            $pathSegementsCount = count($pathSegments);

            if ($segment <= 0) {
                $segment = 1;
            }

            if ($segment > $pathSegementsCount) {
                $segment = $pathSegementsCount;
            }

            $pathSegments[$segment - 1] = $value;
        }

        return $this->setElement('path', implode('/', $pathSegments));
    }

    /**
     * @param mixed $value
     *
     * @return Url
     */
    public function withQueryParam(string $key, $value) : self
    {
        $params = $this->getAllQueryParams() ?? [];
        $params = array_replace_recursive($params, [$key => $value]);

        return $this->setElement('query', http_build_query($params));
    }

    /**
     * @param mixed[] $params
     *
     * @return Url
     */
    public function withQueryParams(array $params) : self
    {
        foreach ($params as $key => $value) {
            $this->withQueryParam($key, $value);
        }

        return $this;
    }

    /**
     * @return Url
     */
    public function withFragment(string $value) : self
    {
        return $this->setElement('fragment', trim($value, '/'));
    }

    /**
     * @return Url
     */
    public function withoutHost() : self
    {
        return $this->setElement('host', '');
    }

    /**
     * @return Url
     */
    public function withoutSubDomain() : self
    {
        $host      = $this->getHost();
        $subDomain = $this->getSubDomain();

        if ($host && $subDomain) {
            $host = str_replace($subDomain . '.', '', $host);

            return $this->setElement('host', $host);
        }

        return $this;
    }

    /**
     * @return Url
     */
    public function withoutPath() : self
    {
        return $this->setElement('path', '');
    }

    /**
     * @return Url
     */
    public function withoutFragment() : self
    {
        return $this->setElement('fragment', '');
    }

    /**
     * @return Url
     */
    public function withoutAllQueryParams() : self
    {
        return $this->setElement('query', '');
    }

    /**
     * @return Url
     */
    public function withoutQueryParam(string $key) : self
    {
        if ($params = $this->getAllQueryParams()) {
            if (isset($params[$key])) {
                unset($params[$key]);
            }

            return $this->setElement('query', http_build_query($params));
        }

        return $this;
    }

    /**
     * @param mixed[] $params
     */
    private function replacePlaceholders(string $uri, array $params = []) : string
    {
        foreach ($params as $key => $val) {
            $uri = str_replace('{' . $key . '}', $val, $uri);
        }

        return $uri;
    }

    /**
     * @return Url
     */
    private function setElement(string $key, string $value) : self
    {
        $this->elements[$key] = $value;

        return $this;
    }

    /**
     * @return string[]|null
     */
    private function getHostParts() : ?array
    {
        if ($host = $this->getHost()) {
            return explode('.', $host);
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    private function getParsedElements(string $elm)
    {
        $elements = $this->parse();

        if ($elements && !empty($elements[$elm])) {
            return $elements[$elm];
        }

        return null;
    }

    /**
     * @return mixed[]|null
     */
    private function parse() : ?array
    {
        if (!$this->elements && $this->url) {
            $parseResponse = parse_url($this->url);

            if (is_array($parseResponse)) {
                $this->elements = $parseResponse;
            }
        }

        return $this->elements;
    }
}
