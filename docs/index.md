# Mail-Admin

Mail-Admin is an open-sourced web (PHP) based administration interface for managing iRedMail mail servers that run on an LDAP backend. It was originally written as a free alternative to [iRedMail-Pro](https://www.iredmail.org/admin_panel.html) but recently we have added features not included in this, such as the user portal.

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
* Administration of [iRedAPD](https://github.com/iredmail/iRedAPD) throttling
* Administration of [iRedAPD](https://github.com/iredmail/iRedAPD) greylisting
* Global, Domain and user level control of amavisd white/black list

## Support
Mail-admin is written to run on a apache or nginx web server with PHP. It was written with PHP 8.0. It does require the php-ldap plugin to function.
It also requires open-ldap (slapd) version 3. Mail-admin does not require a database to function as it stores all it's data from open-ldap or existing databases for other services such as [iRedAPD](https://github.com/iredmail/iRedAPD).

## Requirements
* A complete installation of iRedMail
* Open-LDAP backend
* Apache or Nginx
* PHP 8.0 or later

## Todo list
* Two-factor authentication
* Domain-level custom login
