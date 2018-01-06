# Twitter

*By [endroid](http://endroid.nl/)*

[![Latest Stable Version](http://img.shields.io/packagist/v/endroid/twitter.svg)](https://packagist.org/packages/endroid/twitter)
[![Build Status](https://secure.travis-ci.org/endroid/twitter.png)](http://travis-ci.org/endroid/twitter)
[![Total Downloads](http://img.shields.io/packagist/dt/endroid/twitter.svg)](https://packagist.org/packages/endroid/twitter)
[![Monthly Downloads](http://img.shields.io/packagist/dm/endroid/twitter.svg)](https://packagist.org/packages/endroid/twitter)
[![License](http://img.shields.io/packagist/l/endroid/twitter.svg)](https://packagist.org/packages/endroid/twitter)

This library helps making requests to the Twitter API. The only
things you need are the keys which you can find in the [developer console](https://dev.twitter.com/).

## Installation

Use [Composer](https://getcomposer.org/) to install the library.

``` bash
$ composer require endroid/twitter
```

## Get consumer access key and token from Twitter

Register your application at http://apps.twitter.com/app

## Usage

```php
use Endroid\Twitter\Client;

// If you want to fetch the Twitter API with "application only" authentication, $accessToken and $accessTokenSecret are optional
$twitter = new Client($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

// Retrieve the user's timeline
$tweets = $twitter->getTimeline([
    'count' => 5
]);

// Or retrieve the timeline using the generic query method
$response = $twitter->query('/statuses/user_timeline', 'GET', 'json', $parameters);
$tweets = json_decode($response->getContent());
```

## Symfony integration



## Versioning

Version numbers follow the MAJOR.MINOR.PATCH scheme. Backwards compatibility
breaking changes will be kept to a minimum but be aware that these can occur.
Lock your dependencies for production and test your code when upgrading.

## License

This bundle is under the MIT license. For the full copyright and license
information please view the LICENSE file that was distributed with this source code.
