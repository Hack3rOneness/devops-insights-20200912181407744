#!/bin/bash
#
# Facebook CTF: Provision script for vagrant dev environment
# 
# Usage: ./provision.sh [self | prod]
#

# Things we will use
DB="facebook-ctf"
U="ctf"
P="ctf"
P_ROOT="root"
CTF_PATH="/var/www/facebook-ctf"

source "$CTF_PATH/tools/lib.sh"

# Ascii art is always appreciated
set_motd "$CTF_PATH"

# Off to a good start...
sudo apt-get update

# Instal MySQL
install_mysql "$P_ROOT"

# Install PHP and git
package php5
package php5-mysql
package git

# Install HHVM
install_hhvm "$CTF_PATH"

# Install Composer
install_composer "$CTF_PATH"

# Make sure all apache is gone
sudo service apache2 stop
apt-get autoremove

# Install nginx
if [ -z "$1" ]; then
	install_nginx "$CTF_PATH" "self"
else
	install_nginx "$CTF_PATH" "$1"
fi

# Database creation
import_empty_db "root" "$P_ROOT" "$DB" "$CTF_PATH"

# Make attachments folder world writable
sudo chmod 777 "$CTF_PATH/game/data/attachments"

log 'Facebook-CTF deployment is complete!'

exit 0
# kthxbai
