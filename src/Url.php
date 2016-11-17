<?php
namespace Simplon\Url;

/**
 * Class Url
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
     * @param string $url
     */
    public function __construct(string $url = null)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        if ($scheme = $this->getParsedElements('scheme'))
        {
            return $scheme;
        }

        return 'http';
    }

    /**
     * @return string|null
     */
    public function getHost()
    {
        return $this->getParsedElements('host');
    }

    /**
     * @return string|null
     */
    public function getPort()
    {
        return $this->getParsedElements('port');
    }

    /**
     * @return string|null
     */
    public function getUser()
    {
        return $this->getParsedElements('user');
    }

    /**
     * @return string|null
     */
    public function getPass()
    {
        return $this->getParsedElements('pass');
    }

    /**
     * @return string|null
     */
    public function getPath()
    {
        return $this->getParsedElements('path');
    }

    /**
     * @param int $segment
     *
     * @return string|null
     */
    public function getPathSegment(int $segment)
    {
        if ($path = $this->getParsedElements('path'))
        {
            $pathSegments = explode('/', trim($path, '/'));
            $pathSegementsCount = count($pathSegments);

            if ($segment <= 0)
            {
                $segment = 1;
            }

            if ($segment > $pathSegementsCount)
            {
                $segment = $pathSegementsCount;
            }

            if (!empty($pathSegments[$segment - 1]))
            {
                return $pathSegments[$segment - 1];
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAllQueryParams()
    {
        if ($query = $this->getParsedElements('query'))
        {
            parse_str($query, $params);

            return $params;
        }

        return [];
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function getQueryParam(string $key)
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
    public function getFragment()
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

        $url[] = $this->getScheme() . '://';
        $url[] = $this->getHost();

        if ($port = $this->getPort())
        {
            $url[] = ':' . $port;
        }

        if ($path = $this->getPath())
        {
            $url[] = '/' . $path;
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
    public function withScheme(string $value): self
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
     *
     * @return Url
     */
    public function withPath(string $value): self
    {
        return $this->setElement('path', trim($value, '/'));
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
            $pathSegments = explode('/', trim($this->getPath(), '/'));
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
        $params = array_replace_recursive($this->getAllQueryParams(), [$key => $value]);

        return $this->setElement('query', http_build_query($params));
    }

    /**
     * @param string $key
     *
     * @return Url
     */
    public function withoutQueryParam(string $key): self
    {
        $params = $this->getAllQueryParams();

        if (!empty($params[$key]))
        {
            unset($params[$key]);
        }

        return $this->setElement('query', http_build_query($params));
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
    public function withoutFragment(): self
    {
        return $this->setElement('fragment', '');
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
     * @param string $elm
     *
     * @return mixed
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