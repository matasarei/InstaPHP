# InstaPHP
Instagram API for PHP

## Before you start
You are limited to 5000 requests per hour per access_token or client_id overall. That's why you should set up writable directory to allow caching ("./cache/" by default):
```php
//use cache for 10 minutes and then refresh data
$insta = new InstaPHP($username, $accessToken, 10, './cache/');
```
But you also can init it without cache support:
```php
//no cache
$insta = new InstaPHP($username, $accessToken, false, false);
```
See class methods to discover features.
Instagram docs for more info: https://instagram.com/developer/

## Access token
Get your access token at http://instagram.pixelunion.net/

## Live examples
http://orange-traveler.com/ 
![screenshot 22](https://user-images.githubusercontent.com/6638367/29663348-49ac1500-88d3-11e7-8150-7c394398e354.png)
