# _Film2.0_

_The new way of watching movies, just simple and plain! It's more like a Popcorntime clone, but better :)_

## Usage

_How do I, as a developer, start working on the project?_ 

1. _Use `./run.php` to start YTS searcher._
2. _Use your browser and navigate to `http://ipaddress/film2.0/web/`._

### Important note

_Make sure that the following folders are a member of the server-process. To find out what the server-process name is use this `var_dump(shell_exec("whoami"));` code in a php file and execute it in the browser._

> - cache
> - log
> - poster
> - subtitle

_Some files, including the following, needs to be executable, make sure to `chmod 0755` those files!_

> - `run.php`

## Features

> - Get Torrents from YTS.
> - Get subtitles from YTS.
> - Get subtitles from OpenSubtitles.org.
> - Webinterface.
> - Monitor and start background jobs.
> - Main page caching.
> - Admin page AJaX refreshing.
> - OpenSubtitles.org limit check.

### Future

> - Test page in admin interface.
> - Get Torrents from Torrentz.eu.
> - A wanted list.
> - Main page AJaX (reloading etc.).

### Self notes

Test page:
> - Torrent file (extract data).
> - Valid hash.
> - Scrape Tracker.
> - Get IMDB info.
> - YIFY Subtitle.
> - OpenSubtitles Subtitle.