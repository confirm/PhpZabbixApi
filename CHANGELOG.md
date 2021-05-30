# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [2.5.0](https://github.com/confirm/PhpZabbixApi/compare/2.4.6...2.5.0) - 2021-05-30
* minor #65 Leverage GitHub templates (phansys)
* minor #63 Move remaining CI tasks to Github Actions (phansys)
* minor #64 Replace `__autoload()` with `spl_autoload_register()` (phansys)
* minor #60 Leverage GitHub actions (phansys)
* minor #61 Normalize contents at `composer.json` (phansys)
* minor #59 Remove development files from releases (phansys)
* minor #53 Do not call `userLogin()` at `__construct()` (phansys)
* minor #55 Make authentication check for each API method explicit at build time (phansys)
* minor #41 Leverage constants from `defines.inc.php` (phansys)
* minor #54 Add suggestion for "ext-posix" (phansys)
* minor #52 Add test for `userLogin()` (phansys)
* minor #50 Mark `ZabbixApi` as final (phansys)
* minor #49 Avoid to retrieve a bigger payload than required at `userLogin()` (phansys)
* minor #48 Add more assertions at `testZabbixApiClass()` (phansys)
* bugfix #47 "ext-curl" is not required, request are made with `fopen()` (phansys)
* minor #44 Respect PSR-2 coding standard (phansys)
* minor #39 Add basic support for Travis CI (phansys)
