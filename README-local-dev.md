# Quick start with PHP dev server

The web2 site will run happily under the [PHP development server](http://php.net/manual/en/features.commandline.webserver.php), but you'll need to have a [Redis](http://redis.io) server running on localhost:6379.  You'll also need to configure a suitable [Joind.in API](https://github.com/joindin/joindin-api/); you could use the [live Joind.in API](https://api.joind.in/) or if you're developing against a local version, reference it in config/config.php.  Note that the API will also run under the built-in webserver, but will need to be on a different listening port.

To run the site on http://localhost:8080/, do the following:
```
cd web
php -S localhost:8080 index.php
```
