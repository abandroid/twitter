Endroid Twitter
===============

[![Build Status](https://secure.travis-ci.org/endroid/Twitter.png)](http://travis-ci.org/endroid/Twitter)

Twitter helps making requests to the Twitter API, without having to bother too much about OAuth headers and
building requests. The only things you need are the keys which you can find in the [developer console](https://dev.twitter.com/).

```php
<?php

$twitter = new Endroid\Twitter\Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$timeline = $twitter->getTimeline();

```

## Symfony

You can use [`EndroidTwitterBundle`](https://github.com/endroid/EndroidTwitterBundle) to enable this service in your Symfony application.