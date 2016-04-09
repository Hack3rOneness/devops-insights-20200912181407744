#!/bin/bash
#
# Facebook CTF: Provision script for vagrant dev environment
#
# Usage: ./provision.sh [dev | prod]
#

DB="facebook-ctf"
U="ctf"
P="ctf"
P_ROOT="root"
CTF_PATH="/var/www/facebook-ctf"
MODE=${1:-dev}

source "$CTF_PATH/tools/lib.sh"

log "Provisioning in $MODE mode"

# Ascii art is always appreciated
set_motd "$CTF_PATH"

# Off to a good start...
sudo apt-get update

# Install MySQL
install_mysql "$P_ROOT"

# Install MyCLI
install_mycli

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
install_nginx "$CTF_PATH" "$MODE"


# Database creation
import_empty_db "root" "$P_ROOT" "$DB" "$CTF_PATH" "$MODE"

# Make attachments folder world writable
sudo chmod 777 "$CTF_PATH/game/data/attachments"
sudo chmod 777 "$CTF_PATH/game/data/attachments/deleted"

log 'Facebook-CTF deployment is complete!'

exit 0
# kthxbai
