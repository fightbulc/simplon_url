<?php

use Simplon\Url\Url;

require __DIR__ . '/../vendor/autoload.php';

$url = new Url('http://foobar.com/en/test/challenge?utm_source=source&utm_campaign=campaign&utm_medium=medium#hello-world');
$url = new Url('ftp://peter:sunny@foobar.com:21');

var_dump(
    $url
        ->getPort()
);

var_dump(
    $url
        ->withoutQueryParam('utm_source')
        ->withQueryParam('utm_source', 'foo')
        ->withPath('/foo/bar')
        ->__toString()
);

var_dump(
    $url = (new Url('https://dear-johnny.io/foo/bar?sun=is-shining&training=yes#hello-world'))
        ->withPathSegment(1, 'hoo')
        ->withoutQueryParam('training')
        ->withQueryParam('sun', 'off')
        ->withoutFragment()
        ->__toString()
);

var_dump(
    (new Url())
        ->withScheme('https')
        ->withHost('dear-johnny.io')
        ->withPath('/foo/bar')
        ->withQueryParam('sun', 'is-shining')
        ->withFragment('hello-world')
        ->withoutFragment()
        ->withPathSegment(1, 'hoo')
        ->__toString()
);
