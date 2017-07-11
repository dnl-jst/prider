#!/bin/bash

add-apt-repository -y ppa:ondrej/php

# update package lists
apt-get update

# set mysql password so it isn't prompted during installation
debconf-set-selections <<< 'mysql-server mysql-server/root_password password rootpass'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password rootpass'

# install postfix
debconf-set-selections <<< "postfix postfix/mailname string `hostname -f`"
debconf-set-selections <<< "postfix postfix/main_mailer_type string 'Internet Site'"

# install mysql, nginx, php5-fpm
apt-get install -y mysql-server mysql-client ssl-cert postfix curl vim

# install apache2 with php 5.6
apt-get install -y php7.0 apache2 libapache2-mod-php7.0 php7.0-mysql php7.0-curl php7.0-cli php7.0-zip php7.0-xml php7.0-mbstring php7.0-ssh2

# generate snakeoil ssl certificate
make-ssl-cert generate-default-snakeoil

# copy nginx config
cp -f /vagrant/vagrant/000-default.conf /etc/apache2/sites-available/000-default.conf

# stash old symlink
mv /var/www/html /var/www/html_`date '+%s'`

# mount www-root
ln -s /vagrant/ /var/www/html

# install composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# install projects composer dependencies
cd /vagrant/
composer install

# enable mod_rewrite
a2enmod rewrite

# restart services
service apache2 restart

# create mysql database
echo "CREATE DATABASE IF NOT EXISTS prider" | mysql -uroot -prootpass
echo "GRANT ALL PRIVILEGES ON prider.* TO prider@\`%\` IDENTIFIED BY 'hsfbiuu2';" | mysql -uroot -prootpass