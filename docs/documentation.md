# Documentation
[Home](https://mailadminpanel.org/)

This page contains the documentation for configuring and using the mail-admin panel.
All of the user editable files are located in the 'usr/' directory and this directory will contain the admin logs as well as the main configuration file.
It is not recomended to directly edit files that are not in the 'usr/' directory, as this may cause damaged to mail-admin.

## Configuration file

 usr/config.php

 The configuration file can be found in the 'usr/' directory.
 If this file does not exist, then mail-admin will not know how to work with your mail server.
 If you do not have a config file, you will see the following message ***No config file exists. Make a copy of config.sample.php and add your configuration there.***
 
### Sample config

 usr/config.sample.php
 
 There is an included sample config that can be found in the 'usr/' directory. Create a copy of this file as 'config.php'
 See the [example config](https://github.com/johnhart96/mail-admin/blob/main/usr/config.sample.php) for more details.
 
 ### Variables
| Key              | Data type     | Description                   |
| -----------------|---------------|-------------------------------|
| BRANDING         | String        | Branding name                 |
| MAILQUOTA        | Integer       | Default mailbox size in bytes |
| DEBUG            | BOOLEAN       | Show error reporting data     |
| APP_MAIL         | String        | URL to webmail                |
| APP_DRIVE        | String        | URL to web drive              |
| LDAP_SERVER      | String        | LDAP Server location          |
| LDAP_BASEDN      | String        | Base DN of the LDAP Server    |
| LDAP_ADMINUSER   | String        | CN of the LDAP admin user     |
| LDAP_ADMINPASSWD | String        | Password for LDAP admin user  |
| LDAP_DOMAINDN    | String        | CN of domains in LDAP         |
| IAPD_HOST        | String        | iRedAPD database host         |
| IAPD_USER        | String        | iRedAPD database user         |
| IAPD_PASSWORD    | String        | iRedAPD database password     |

## Plugins
Mail-admin has the ability to add your own plugins. These can be added in the 'plugins/' directory and are all contained in their own folders.
***(more info to come soon)***

## Nextcloud
Mail-admin can integrate with Nextcloud when it uses the same LDAP server as the mail server. 
Each user has a service called 'Nextcloud' that you can enable on a user by user bases. This adds the ***enabledservice=nextcloud*** atribute to the user account. You will need to add the following user query in your LDAP settings under Settings > LDAP/AD integration.

 (|(enabledservice=nextcloud))
 
![LDAP settings in Nextcloud](https://github.com/johnhart96/mail-admin/raw/main/docs/ldap.PNG)
