#!/bin/bash
#
# Facebook CTF: Provision script for vagrant dev environment
#

# Remove apache configuration
rm /etc/apache2/sites-enabled/scotchbox.local.conf

# Apply our apache configuration
cp /var/www/facebook-ctf/tools/000-default.conf /etc/apache2/sites-enabled/000-default.conf

# Restart apache
service apache2 restart

# Database creation
mysql -u root --password='root' -e 'CREATE DATABASE `facebook-ctf`;'

# Database schema creation
mysql -u root --password='root' 'facebook-ctf' -e 'source /var/www/facebook-ctf/database/schema.sql;'
mysql -u root --password='root' 'facebook-ctf' -e 'source /var/www/facebook-ctf/database/countries.sql;'
mysql -u root --password='root' 'facebook-ctf' -e 'source /var/www/facebook-ctf/database/logos.sql;'

# Database user creation
mysql -u root --password='root' -e 'CREATE USER "ctf"@"localhost" IDENTIFIED BY "ctf";'
mysql -u root --password='root' -e 'GRANT ALL PRIVILEGES ON `facebook-ctf`.* TO "ctf"@"localhost";'
mysql -u root --password='root' -e 'FLUSH PRIVILEGES;'

# Ascii art is always appreciated
echo '   __               _                 _     _____ _______ ______  '  > /etc/motd.tail
echo '  / _|             | |               | |   / ____|__   __|  ____| ' >> /etc/motd.tail
echo ' | |_ __ _  ___ ___| |__   ___   ___ | | _| |       | |  | |__    ' >> /etc/motd.tail
echo ' |  _/ _` |/ __/ _ \  _ \ / _ \ / _ \| |/ / |       | |  |  __|   ' >> /etc/motd.tail
echo ' | || (_| | (_|  __/ |_) | (_) | (_) |   <| |____   | |  | |      ' >> /etc/motd.tail
echo ' |_| \__,_|\___\___|_.__/ \___/ \___/|_|\_\\_____|  |_|  |_|      ' >> /etc/motd.tail
echo '                                                                  ' >> /etc/motd.tail
echo '                                                                  ' >> /etc/motd.tail

# kthxbai
