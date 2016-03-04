#!/bin/bash
#
# Facebook CTF: Provision script for vagrant dev environment
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
install_nginx "$CTF_PATH" "prod"

# Database creation
import_empty_db "root" "$P_ROOT" "$DB" "$CTF_PATH"

# Make attachments folder world writable
sudo chmod 777 "$CTF_PATH/game/data/attachments"

log 'Facebook-CTF development is complete!'

exit 0
# kthxbai
