# InstaPHP
Instagram API for PHP

## Before you start
You are limited to 5000 requests per hour per access_token or client_id overall. Thant's why you should set up writable directory to allow the class savea and process caches ("./cache/" by default):
```php
//use cache for 10 minutes and than refresh data
$insta = new InstaPHP($username, $accessToken, $cacheTime = 10, $cachePath = './cache/');
```
But, you also can init class without cache support:
```php
//no cache
$insta = new InstaPHP($username, $accessToken, false, false);
```
See class methods to discover features.
Instagram docs for more info: https://instagram.com/developer/

## Access token
Get your access token at http://instagram.pixelunion.net/
