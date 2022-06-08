# Welcome

Mail-Admin is an open-sourced web (PHP) based administration interface for managing postfix/dovecot mail servers that run on an LDAP backend. It was originally written as a free alternative to [iRedMail-Pro](https://www.iredmail.org/admin_panel.html) but recently we have added features that are not included in this. Such as the user portal.

## Pages
* [Documentation](./documentation)
* [Installation](./install)

## Features
* Domain level administration
* Global server level administration
* Reverse DNS White/Black list
* Quota support
* User level service, enable/disable
* End user portal
* Branding customization
* Direct replacement for iRedAdmin-Pro
* Create, Edit, Delete for domains, users, aliases and groups
* Administration of [iRedAPD](https://github.com/iredmail/iRedAPD)

## Support
Mail-admin is written to run on a apache or nginx web server with PHP. It was written with PHP 8.0 but has been tested as low as PHP 6. It does require the php-ldap plugin to function.
It also requires open-ldap (slapd) version 3. Mail-admin does not require a database to function as it stores all it's data from open-ldap or existing databases for other services such as [iRedAPD](https://github.com/iredmail/iRedAPD).

## Requirements
* Apache >=2.2 or Nginx (not tested with nginx)
* PHP >= php 6.0 <= php 8.0

## Todo list
* Add support for amavisd white & blacklists
* Two factor authentication
* Domain level custom login
