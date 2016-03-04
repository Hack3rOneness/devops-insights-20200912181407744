#!/bin/bash
#
# Facebook CTF: Provision script for vagrant dev environment
#

# Stop apache
service apache2 stop

# Install HHVM
apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0x5a16e7281be7a449
add-apt-repository "deb http://dl.hhvm.com/ubuntu $(lsb_release -sc) main"
apt-get update
apt-get install hhvm -y

# Remove apache configuration
rm /etc/apache2/sites-enabled/scotchbox.local.conf

# Copy HHVM configuration
cp /var/www/facebook-ctf/tools/server.hhvm /etc/hhvm/server.ini

# Enable HHVM globally
/usr/bin/update-alternatives --install /usr/bin/php php /usr/bin/hhvm 60

# Install Composer
cd /var/www/facebook-ctf
curl -sS https://getcomposer.org/installer | php
php composer.phar install
mv composer.phar /usr/local/bin

# Restart HHVM
service hhvm restart

# Make HHVM to start on restart
update-rc.d hhvm defaults

# Disable apache to start
update-rc.d apache2 disable

# Apply our apache configuration
#cp /var/www/facebook-ctf/tools/fbctf.conf /etc/apache2/sites-available/fbctf.conf
#rm -Rf /etc/apache2/sites-enabled/*.conf
#ln -s /etc/apache2/sites-available/fbctf.conf /etc/apache2/sites-enabled/fbctf.conf

# Restart apache
#service apache2 restart

# Database creation
DB="facebook-ctf"
echo "Creating DB - $DB"
mysql -u root --password='root' -e "CREATE DATABASE \`$DB\`;"

# Database schema creation
echo "DB: Importing schema..."
mysql -u root --password='root' "$DB" -e "source /var/www/facebook-ctf/database/schema.sql;"
echo "DB: Importing countries..."
mysql -u root --password='root' "$DB" -e "source /var/www/facebook-ctf/database/countries.sql;"
echo "DB: Importing logos..."
mysql -u root --password='root' "$DB" -e "source /var/www/facebook-ctf/database/logos.sql;"

# Database user creation
U="ctf"
P="ctf"
echo "DB: Creating user..."
mysql -u root --password='root' -e "CREATE USER '$U'@'localhost' IDENTIFIED BY '$P';"
mysql -u root --password='root' -e "GRANT ALL PRIVILEGES ON \`$DB\`.* TO '$U'@'localhost';"
mysql -u root --password='root' -e "FLUSH PRIVILEGES;"

# Create settings.ini
echo "Generating settings.ini"
cat /var/www/facebook-ctf/common/settings.ini.example | sed "s/DATABASE/$DB/g" | sed "s/MYUSER/$U/g" | sed "s/MYPWD/$P/g" > /var/www/facebook-ctf/common/settings.ini

# Ascii art is always appreciated
echo '   __               _                 _     _____ _______ ______  '  > /etc/motd.tail
echo '  / _|             | |               | |   / ____|__   __|  ____| ' >> /etc/motd.tail
echo ' | |_ __ _  ___ ___| |__   ___   ___ | | _| |       | |  | |__    ' >> /etc/motd.tail
echo ' |  _/ _` |/ __/ _ \  _ \ / _ \ / _ \| |/ / |       | |  |  __|   ' >> /etc/motd.tail
echo ' | || (_| | (_|  __/ |_) | (_) | (_) |   <| |____   | |  | |      ' >> /etc/motd.tail
echo ' |_| \__,_|\___\___|_.__/ \___/ \___/|_|\_\\_____|  |_|  |_|      ' >> /etc/motd.tail
echo '                                                                  ' >> /etc/motd.tail
echo '                                                                  ' >> /etc/motd.tail

# Add admin user to the game to start administering
PASSWORD=$(date +%s | md5sum | cut -d" " -f1)
echo "The password for admin is: $PASSWORD"
HASH=$(echo -n "$PASSWORD" | sha256sum | cut -d" " -f1)
echo "DB: Inserting admin user..."
mysql -u root --password='root' "$DB" -e "INSERT INTO teams (name, password, admin, logo, created_ts) VALUES('admin', '$HASH', 1, 'admin', NOW());"

# kthxbai
