<?php

namespace Simplon\Url;

/**
 * @package Simplon\Url
 */
class Url
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var array
     */
    private $elements;

    /**
     * @return string
     */
    public static function getCurrentUrl(): string
    {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * @param string $url
     */
    public function __construct(string $url = null)
    {
        if ($url)
        {
            $this->url = $url;
            $this->parse();
        }
    }

    /**
     * @return null|string
     */
    public function getProtocol(): ?string
    {
        if ($scheme = $this->getParsedElements('scheme'))
        {
            return $scheme;
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->getParsedElements('host');
    }

    /**
     * @return string|null
     */
    public function getSubDomain(): ?string
    {
        $parts = $this->getHostParts();

        if ($parts)
        {
            array_pop($parts);
            array_pop($parts);

            return implode('.', $parts);
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        $parts = $this->getHostParts();

        if ($parts)
        {
            return array_slice($parts, -2, 1)[0];
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getTopLevelDomain(): ?string
    {
        $parts = $this->getHostParts();

        if ($parts)
        {
            return array_slice($parts, -1, 1)[0];
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getPort(): ?string
    {
        return $this->getParsedElements('port');
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->getParsedElements('user');
    }

    /**
     * @return string|null
     */
    public function getPass(): ?string
    {
        return $this->getParsedElements('pass');
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        $path = trim($this->getParsedElements('path'), '/');

        return '/' . $path;
    }

    /**
     * @param int $segment
     *
     * @return string|null
     */
    public function getPathSegment(int $segment): ?string
    {
        if ($path = $this->getParsedElements('path'))
        {
            $pathSegments = explode('/', trim($path, '/'));
            $pathSegementsCount = count($pathSegments);

            if ($segment <= 0)
            {
                $segment = 1;
            }

            if ($segment <= $pathSegementsCount && !empty($pathSegments[$segment - 1]))
            {
                return $pathSegments[$segment - 1];
            }
        }

        return null;
    }

    /**
     * @return array|null
     */
    public function getAllQueryParams(): ?array
    {
        if ($query = $this->getParsedElements('query'))
        {
            parse_str($query, $params);

            return $params;
        }

        return null;
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function getQueryParam(string $key): ?string
    {
        if (($params = $this->getAllQueryParams()) && isset($params[$key]))
        {
            return $params[$key];
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getFragment(): ?string
    {
        return $this->getParsedElements('fragment');
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $url = [];

        if ($this->getUser() && $this->getPass())
        {
            $url[] = $this->getUser() . ':' . $this->getPass() . '@';
        }

        if ($host = $this->getHost())
        {
            if ($this->getProtocol())
            {
                $url[] = $this->getProtocol() . ':';
            }

            $url[] = '//' . $this->getHost();
        }

        if ($port = $this->getPort())
        {
            $url[] = ':' . $port;
        }

        if ($path = $this->getPath())
        {
            $path = trim($path, '/');

            if ($path)
            {
                $url[] = '/' . $path;
            }
        }

        if ($params = $this->getAllQueryParams())
        {
            ksort($params);
            $url[] = '?' . http_build_query($params);
        }

        if ($fragment = $this->getFragment())
        {
            $url[] = '#' . $fragment;
        }

        return implode('', $url);
    }

    /**
     * @param string $value
     *
     * @return Url
     */
    public function withProtocol(string $value): self
    {
        return $this->setElement('scheme', $value);
    }

    /**
     * @param string $value
     *
     * @return Url
     */
    public function withHost(string $value): self
    {
        return $this->setElement('host', $value);
    }

    /**
     * @param string $value
     *
     * @return Url
     */
    public function withSubDomain(string $value): self
    {
        $host = $value . '.' . $this->getHost();

        if ($subDomain = $this->getSubDomain())
        {
            $host = str_replace($this->getSubDomain(), $value, $this->getHost());
        }

        return $this->setElement('host', $host);
    }

    /**
     * @param string $value
     *
     * @return Url
     */
    public function withDomain(string $value): self
    {
        $host = str_replace($this->getDomain(), $value, $this->getHost());

        return $this->setElement('host', $host);
    }

    /**
     * @param string $value
     *
     * @return Url
     */
    public function withTopLevelDomain(string $value): self
    {
        $host = str_replace($this->getTopLevelDomain(), $value, $this->getHost());

        return $this->setElement('host', $host);
    }

    /**
     * @param string $value
     *
     * @return Url
     */
    public function withPort(string $value): self
    {
        return $this->setElement('port', $value);
    }

    /**
     * @param string $value
     *
     * @return Url
     */
    public function withUser(string $value): self
    {
        return $this->setElement('user', $value);
    }

    /**
     * @param string $value
     *
     * @return Url
     */
    public function withPass(string $value): self
    {
        return $this->setElement('pass', $value);
    }

    /**
     * @param string $value
     * @param array|null $params
     *
     * @return Url
     */
    public function withPath(string $value, ?array $params = null): self
    {
        $path = rtrim($value, '/');

        if ($params)
        {
            $path = $this->replacePlaceholders($path, $params);
        }

        return $this->setElement('path', $path);
    }

    /**
     * @param string $value
     * @param array|null $params
     *
     * @return Url
     */
    public function withPrefixPath(string $value, ?array $params = null): self
    {
        $path = rtrim($value, '/') . '/' . trim($this->getPath(), '/');

        if ($params)
        {
            $path = $this->replacePlaceholders($path, $params);
        }

        return $this->setElement('path', $path);
    }

    /**
     * @param string $value
     * @param array|null $params
     *
     * @return Url
     */
    public function withTrailPath(string $value, ?array $params = null): self
    {
        $path = rtrim($this->getPath(), '/') . '/' . trim($value, '/');

        if ($params)
        {
            $path = $this->replacePlaceholders($path, $params);
        }

        return $this->setElement('path', $path);
    }

    /**
     * @param int $segment
     * @param string $value
     *
     * @return Url
     */
    public function withPathSegment(int $segment, string $value): self
    {
        // fallback
        $pathSegments = [$value];

        if ($path = $this->getPath())
        {
            $pathSegments = explode('/', rtrim($this->getPath(), '/'));
            $pathSegementsCount = count($pathSegments);

            if ($segment <= 0)
            {
                $segment = 1;
            }

            if ($segment > $pathSegementsCount)
            {
                $segment = $pathSegementsCount;
            }

            $pathSegments[$segment - 1] = $value;
        }

        return $this->setElement('path', implode('/', $pathSegments));
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return Url
     */
    public function withQueryParam(string $key, $value): self
    {
        $params = $this->getAllQueryParams() ?? [];
        $params = array_replace_recursive($params, [$key => $value]);

        return $this->setElement('query', http_build_query($params));
    }

    /**
     * @param array $params
     *
     * @return Url
     */
    public function withQueryParams(array $params): self
    {
        foreach ($params as $key => $value)
        {
            $this->withQueryParam($key, $value);
        }

        return $this;
    }

    /**
     * @param string $value
     *
     * @return Url
     */
    public function withFragment(string $value): self
    {
        return $this->setElement('fragment', trim($value, '/'));
    }

    /**
     * @return Url
     */
    public function withoutHost(): self
    {
        return $this->setElement('host', '');
    }

    /**
     * @return Url
     */
    public function withoutSubDomain(): self
    {
        $host = $this->getHost();

        if ($this->getSubDomain())
        {
            $host = str_replace($this->getSubDomain() . '.', '', $host);
        }

        return $this->setElement('host', $host);
    }

    /**
     * @return Url
     */
    public function withoutPath(): self
    {
        return $this->setElement('path', '');
    }

    /**
     * @return Url
     */
    public function withoutFragment(): self
    {
        return $this->setElement('fragment', '');
    }

    /**
     * @return Url
     */
    public function withoutAllQueryParams(): self
    {
        return $this->setElement('query', '');
    }

    /**
     * @param string $key
     *
     * @return Url
     */
    public function withoutQueryParam(string $key): self
    {
        if ($params = $this->getAllQueryParams())
        {
            if (isset($params[$key]))
            {
                unset($params[$key]);
            }

            return $this->setElement('query', http_build_query($params));
        }

        return $this;
    }

    /**
     * @param string $uri
     * @param array $params
     *
     * @return string
     */
    private function replacePlaceholders(string $uri, array $params = []): string
    {
        foreach ($params as $key => $val)
        {
            $uri = str_replace('{' . $key . '}', $val, $uri);
        }

        return $uri;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return Url
     */
    private function setElement(string $key, string $value): self
    {
        $this->elements[$key] = $value;

        return $this;
    }

    /**
     * @return array|null
     */
    private function getHostParts(): ?array
    {
        if ($this->getHost())
        {
            return explode('.', $this->getHost());
        }

        return null;
    }

    /**
     * @param string $elm
     *
     * @return mixed|null
     */
    private function getParsedElements(string $elm)
    {
        if (($elements = $this->parse()) && !empty($elements[$elm]))
        {
            return $elements[$elm];
        }

        return null;
    }

    /**
     * @return array|mixed
     */
    private function parse()
    {
        if (!$this->elements)
        {
            $this->elements = parse_url($this->url);
        }

        return $this->elements;
    }
}