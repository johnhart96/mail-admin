# New Install
## iRedMail install with no existing web server installed
When you install iRedMail, make sure that you install with no web server to follow these instructions.

    sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/sury-php.list
    wget -qO - https://packages.sury.org/php/apt.gpg | sudo apt-key add -
    sudo apt update
    sudo apt install apache2 php8.0 php8.0-ldap git -y
    cd /var/www/html
    wget https://github.com/johnhart96/mail-admin/archive/refs/tags/1.0.zip
    unzip mail-admin-1.0.zip
    mv mail-admin-1.0 mail-admin
    sudo cp /var/www/html/mail-admin/usr/config.sample.php /var/www/html/mail-admin/usr/config.php
 
 Then edit */var/www/html/mail-admin/usr/config.php* and add your LDAP details that you find in *~/iRedmail-xx/iRedMail.tips*
## iRedMail with Nginx
    cd /var/www/html
    wget https://github.com/johnhart96/mail-admin/archive/refs/tags/1.0.zip
    unzip mail-admin-1.0.zip
    mv mail-admin-1.0 mail-admin
    sudo cp /var/www/html/mail-admin/usr/config.sample.php /var/www/html/mail-admin/usr/config.php

# Updating
If you used git to clone the repository to your server, you should be able to run the following command to update to the latest version
    
    cd /var/www/html/mail-admin
    sudo git pull

If you used a package to install, then follow the instructions for a new install but do not override/delete your usr/ directory
