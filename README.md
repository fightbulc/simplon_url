```
     _                 _                          _ 
 ___(_)_ __ ___  _ __ | | ___  _ __    _   _ _ __| |
/ __| | '_ ` _ \| '_ \| |/ _ \| '_ \  | | | | '__| |
\__ \ | | | | | | |_) | | (_) | | | | | |_| | |  | |
|___/_|_| |_| |_| .__/|_|\___/|_| |_|  \__,_|_|  |_|
                |_|                                 
```

# Simplon Url

Straight forward URL parser and builder.

-------------------------------------------------

# Requirements

- PHP7.2+

# Parsing

### HTTP and alike

Parse URLs is as simple as the following example:

```php
$url = new Url(
    'http://foo.bar.com/en/test/challenge?utm_source=source&utm_campaign=campaign&utm_medium=medium#hello-world'
);

$url->getProtocol(); // http
$url->getHost(); // foo.bar.com
$url->getSubDomain(); // foo
$url->getDomain(); // bar
$url->getTopLevelDomain(); // com
$url->getPath(); // /en/test/challenge
$url->getAllQueryParams(); // ['utm_source' => 'source', ...]
$url->getQueryParam('utm_source'); // source
$url->getPathSegment(1); // en
$url->getPathSegment(2); // test
$url->getFragment(); // hello-world
```

### FTP and alike

```php
$url = new Url(
    'ftp://peter:sunny@foobar.com:21'
);

$url->getUser(); // peter
$url->getPass(); // sunny
$url->getPort(); // 21
```

-------------------------------------------------

# Building / Manipulating

You can build a URL from scratch or manipulate from an existing one.
 
### Building a new one

```php
$url = (new Url())
    ->withProtocol('https')
    ->withHost('dear-johnny.io')
    ->withPath('/foo/bar')
    ->withQueryParam('sun', 'is-shining')
    ->withQueryParam('training', 'yes')
    ->withFragment('hello-world');

echo $url; // https://dear-johnny.io/foo/bar?sun=is-shining&training=yes#hello-world
```

### Manipulate an existing one

```php
$url = new Url(
    'https://us.dear-johnny.io/foo/bar?sun=is-shining&training=yes#hello-world'
);

$url
    ->withoutSubDomain()
    ->withTopLevelDomain('com')
    ->withPathSegment(1, 'hoo')
    ->withPrefixPath('/en')
    ->withTrailPath('/much/more')
    ->withoutQueryParam('training')
    ->withQueryParam('sun', 'off')
    ->withoutFragment();

echo $url; // https://dear-johnny.com/en/hoo/bar/much/more?sun=off
```

### Working with path placeholders

Path placeholders work for the following path manipulations. Placeholders are optional and work as following:

```php
//
// withPath
//

$route = '/say/{message}';
$url = new Url('https://foobar.io');
$url->withPath($route, ['message' => 'hello']);
echo $url; // https://foobar.io/say/hello

//
// withPrefixPath
//

$route = '/hello/{message}';
$url = new Url('https://foobar.io/bob');
$url->withPrefixPath($route, ['message' => 'there']);
echo $url; // https://foobar.io/hello/there/bob

//
// withTrailPath
//

$route = '/got/{count}/{item}';
$url = new Url('https://foobar.io/bob');
$url->withTrailPath($route, ['count' => 'five', 'item' => 'cars']);
echo $url; // https://foobar.io/bob/got/five/cars 
```

-------------------------------------------------

# License

Simplon Url is freely distributable under the terms of the MIT license.

Copyright (c) 2017 Tino Ehrich ([tino@bigpun.me](mailto:tino@bigpun.me))

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.