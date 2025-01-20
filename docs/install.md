# Installation
[Home](https://mailadminpanel.org/)


There are many different senarios that you can install and use mail-admin.
The recommended way is to use [iRedmail](https://iredmail.org) to install your mail server. But install iRedMail with no web server.

Even know mail-admin was originally designed to work on top of [iRedmail](https://iredmail.org), it is not exclusive. mail-admin will work with any postifx/dovecot install that uses an LDAP backend.
However, you may need to spend more time configuring.


## Step 1 - Install iRedMail
Before you begin, make sure that you have a functioning installation of iRedMail. **We recommend installing without any web server** and using the openldap backend. If you don't use the openldap backend. then this is not the place for you.
For more information on how to install iRedMail, checkout the following guides for your OS:
* [Debian or Ubuntu](https://docs.iredmail.org/install.iredmail.on.debian.ubuntu.html)
* [Red Hat or CentOS](https://docs.iredmail.org/install.iredmail.on.rhel.html)
* [FreeBSD](https://docs.iredmail.org/install.iredmail.on.freebsd.html)
* [OpenBSD](https://docs.iredmail.org/install.iredmail.on.openbsd.html)
### Step 2 - Download mail-admin

    sudo apt update
    sudo apt install nginx php php-ldap git -y
    cd /var/www
    wget [https://github.com/johnhart96/mail-admin/archive/refs/tags/1.0.zip](https://github.com/johnhart96/mail-admin.git)
    
## Step 3 - Setup the nginx proxy
Make */etc/nginx/sites-enabled/default* look like this:

    server {
        listen 80 default_server;
        root /var/www/mail-admin/;
        server_name _;
        index index.php;
        location / {
            try_files $uri $uri/ =404;
        }
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        }
    }

## Step 4 - Create and edit your config file

    sudo cp /var/www/html/mail-admin/usr/config.sample.php /var/www/html/mail-admin/usr/config.php

Then edit */var/www/html/mail-admin/usr/config.php* and add your LDAP & database details that you find in *~/iRedmail-xx/iRedMail.tips*
