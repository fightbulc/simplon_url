<?php

use PHPUnit\Framework\TestCase;
use Simplon\Url\Url;

class UrlTest extends TestCase
{
    const URL = 'http://jimmybuttler.dev/en/moom/dd21-mqs90-challenge-n/intro?token=GXVQUSNIN48B';

    public function testHttp()
    {
        $url = new Url(self::URL);

        $this->assertEquals('http', $url->getProtocol());
        $this->assertNotEmpty($url->getHost());
        $this->assertEquals('jimmybuttler.dev', $url->getHost());
        $this->assertEquals('/en/moom/dd21-mqs90-challenge-n/intro', $url->getPath());
        $this->assertEquals('en', $url->getPathSegment(1));
        $this->assertEquals('moom', $url->getPathSegment(2));
        $this->assertEquals('dd21-mqs90-challenge-n', $url->getPathSegment(3));
        $this->assertEquals('intro', $url->getPathSegment(4));
        $this->assertEquals('GXVQUSNIN48B', $url->getQueryParam('token'));
    }

    public function testChangeUrl()
    {
        $url = new Url(self::URL);
        $url->withProtocol('https');
        $url->withPath('/de/foo/bar');
        $url->withoutAllQueryParams();

        $this->assertEquals('https', $url->getProtocol());
        $this->assertNotEmpty($url->getHost());
        $this->assertEquals('jimmybuttler.dev', $url->getHost());
        $this->assertEquals('/de/foo/bar', $url->getPath());
        $this->assertEquals('de', $url->getPathSegment(1));
        $this->assertEquals('foo', $url->getPathSegment(2));
        $this->assertEquals('bar', $url->getPathSegment(3));
        $this->assertEmpty($url->getAllQueryParams());
    }

    public function testUserPass()
    {
        $url = new Url('ftp://foo:bar@jimmybuttler.dev/some/path');

        $this->assertEquals('ftp', $url->getProtocol());
        $this->assertEquals('foo', $url->getUser());
        $this->assertEquals('bar', $url->getPass());
        $this->assertNotEmpty($url->getHost());
        $this->assertEquals('jimmybuttler.dev', $url->getHost());
        $this->assertEquals('/some/path', $url->getPath());
        $this->assertEquals('some', $url->getPathSegment(1));
        $this->assertEquals('path', $url->getPathSegment(2));
    }

    public function testWithoutProtocal()
    {
        $url = new Url('//jimmybuttler.dev/some/path');

        $this->assertNotEmpty($url->getHost());
        $this->assertEquals('jimmybuttler.dev', $url->getHost());
        $this->assertEquals('/some/path', $url->getPath());
        $this->assertEquals('some', $url->getPathSegment(1));
        $this->assertEquals('path', $url->getPathSegment(2));
    }

    public function testDomainWithoutSubdomainParts()
    {
        $url = new Url('http://foobar.com');
        $this->assertEquals('foobar', $url->getDomain());
        $this->assertEquals('com', $url->getTopLevelDomain());

        $url->withoutSubDomain();
        $this->assertEquals('foobar.com', $url->getHost());
    }

    public function testDomainWithSubdomainParts()
    {
        $url = new Url('http://lalala.foobar.com');

        $this->assertEquals('lalala', $url->getSubDomain());
        $this->assertEquals('foobar', $url->getDomain());
        $this->assertEquals('com', $url->getTopLevelDomain());

        $url->withoutSubDomain();
        $this->assertEquals('foobar.com', $url->getHost());
    }

    public function testEmptyPath()
    {
        $url = new Url('http://lalala.foobar.com');
        $this->assertEquals('/', $url->getPath());
    }

    public function testPathWithPlaceholders()
    {
        $url = new Url('http://lalala.foobar.com');
        $url->withPath('hello/{name}', ['name' => 'peter']);
        $this->assertEquals('/hello/peter', $url->getPath());

        $url->withPrefixPath('say');
        $this->assertEquals('/say/hello/peter', $url->getPath());
    }

    public function testPrefixPath()
    {
        $url = new Url('http://lalala.foobar.com/hello');
        $url->withPrefixPath('say');
        $this->assertEquals('/say/hello', $url->getPath());
        $url->withoutPath();

        $url->withPrefixPath('say/{message}', ['message' => 'howdy']);
        $this->assertEquals('/say/howdy', $url->getPath());
    }

    public function testTrailingPath()
    {
        $url = new Url('http://lalala.foobar.com/hello');
        $url->withTrailPath('there');
        $this->assertEquals('/hello/there', $url->getPath());
        $url->withoutPath();

        $url->withTrailPath('hello/{message}', ['message' => 'everybody']);
        $this->assertEquals('/hello/everybody', $url->getPath());
    }

    public function testQueryParams()
    {
        $url = new Url('http://lalala.foobar.com/?foo=bar&product=water');
        $this->assertArrayHasKey('product', $url->getAllQueryParams());
        $this->assertEquals('water', $url->getQueryParam('product'));

        $url = new Url('http://lalala.foobar.com');
        $this->assertEquals(null, $url->getAllQueryParams());
        $this->assertEquals(null, $url->getQueryParam('foo'));

        $url = new Url('http://lalala.foobar.com');
        $url->withQueryParam('foo', 'bar');
        $this->assertEquals('bar', $url->getQueryParam('foo'));
    }
}