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

# install apache2 with php 7.2
apt-get install -y php7.2 apache2 libapache2-mod-php7.2 php7.2-mysql php7.2-curl php7.2-cli php7.2-zip php7.2-xml php7.2-mbstring php7.2-ssh2

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

# create database
bin/console do:da:cr --if-not-exists

# load migrations
bin/console do:mi:mi