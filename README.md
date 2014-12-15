# _Film2.0_

_Description: the new way of watching movies, just simple and plain! It's more like a Popcorntime clone, but better :)_

## Usage

_How do I, as a developer, start working on the project?_ 

1. _Use `./run.php` to start YTS searcher._
2. _Use your browser and navite to `http://ipaddress/film2.0/web/`._

### Important note

_Make sure that the following folders are a member of the server-process. To find out what the server-process name is use this (`var_dump(shell_exec("whoami"));`) code in a php file and execute it in the browser._

> - cache
> - log
> - poster
> - subtitle

_Some files, including the following, needs to be executable, make sure to `chmod 0755` those files!_

> - `run.php`

## Features

1. _Get Torrents from YTS._
2. _Get subtitles from YTS._
3. _Get subtitles from OpenSubtitles.org._
4. _Webinterface._
5. _Monitor and start background jobs._

### Future

> - Admin interface (extend).
> - Get Torrents from Torrentz.eu.
> - A wanted list.
> - Caching + AJaX refreshing.

### Self notes

> - Don't display page after submitting, cleaning or deleting in admin.php.