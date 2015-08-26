Twitter
=======

*By [endroid](http://endroid.nl/)*

[![Build Status](https://secure.travis-ci.org/endroid/Twitter.png)](http://travis-ci.org/endroid/Twitter)
[![Latest Stable Version](https://poser.pugx.org/endroid/twitter/v/stable.png)](https://packagist.org/packages/endroid/twitter)
[![Total Downloads](https://poser.pugx.org/endroid/twitter/downloads.png)](https://packagist.org/packages/endroid/twitter)

This library helps making requests to the Twitter API, without having to bother too much about OAuth headers and
building requests. The only things you need are the keys which you can find in the [developer console](https://dev.twitter.com/).

## Installation

Use [Composer](https://getcomposer.org/) to install the library.

``` bash
$ composer require endroid/twitter
```

## Get consumer access key and token from Twitter

Register your application at http://apps.twitter.com/app

## Usage

```php
<?php

use Endroid\Twitter\Twitter;

$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

// Retrieve the user's timeline
$tweets = $twitter->getTimeline(array(
    'count' => 5
));

// Or retrieve the timeline using the generic query method
$response = $twitter->query('statuses/user_timeline', 'GET', 'json', $parameters);
$tweets = json_decode($response->getContent());

```

## Symfony

You can use [`EndroidTwitterBundle`](https://github.com/endroid/EndroidTwitterBundle) to enable this service in your Symfony
application or to expose the Twitter API through your own domain.

## Versioning

Version numbers follow the MAJOR.MINOR.PATCH scheme. Backwards compatible
changes will be kept to a minimum but be aware that these can occur. Lock
your dependencies for production and test your code when upgrading.

## License

This bundle is under the MIT license. For the full copyright and license
information please view the LICENSE file that was distributed with this source code.
