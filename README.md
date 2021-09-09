# mail-admin
An alternative administrator panel for iRedMail (https://iredmail.org/) LDAP or another LDAP based mail service.
mail-admin is written in PHP and uses the php-ldap extension. Its written with PHP 8.0 but it should work no problem on version as old as php6.0. Currently mail-admin is not as full featured as the expensive iRedMail Pro admin panel, but we will get there. Hopefully with some help from our Open-sourced community.
## Testing
* Only tested with open-ldap 3
* Tested on PHP 8.0, 7.4 and 7.3
* Tested on iRedMail (LDAP edition)

## Requirements
* Apache >=2.2 or Nginx (not tested with nginx)
* PHP >= php 6.0 <= php 8.0

## Installation
### iRedMail install with no existing web server installed
When you install iRedMail, make sure that you install with no web server to follow these instructions.

    sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/sury-php.list
    wget -qO - https://packages.sury.org/php/apt.gpg | sudo apt-key add -
    sudo apt update
    sudo apt install apache2 php8.0 php8.0-ldap git -y
    cd /var/www/html
    sudo git clone https://github.com/johnhart96/mail-admin
    sudo cp /var/www/html/mail-admin/usr/config.sample.php /var/www/html/mail-admin/usr/config.php
 
 Then edit */var/www/html/mail-admin/usr/config.php* and add your LDAP details that you find in *~/iRedmail-xx/iRedMail.tips*
### iRedMail with Nginx
    cd /var/www/html
    sudo git clone https://github.com/johnhart96/mail-admin
    sudo cp /var/www/html/mail-admin/usr/config.sample.php /var/www/html/mail-admin/usr/config.php
 Then edit */var/www/html/mail-admin/usr/config.php* and add your LDAP details that you find in *~/iRedmail-xx/iRedMail.tips*
## Updating
If you used git to clone the repository to your server, you should be able to run the following command to update to the latest version
    
    cd /var/www/html/mail-admin
    sudo git pull
