#!/bin/bash
#
# Facebook CTF: Provision script for vagrant dev environment
#
# Usage: ./provision.sh [dev | prod] [path_to_code]
#

DB="facebook-ctf"
U="ctf"
P="ctf"
P_ROOT="root"
CTF_PATH="/var/www/facebook-ctf"
MODE=${1:-dev}
CODE_PATH=${2:-/vagrant}


echo "[+] Provisioning in $MODE mode"

echo "[+] Creating code folder $CTF_PATH"
[[ -d "$CTF_PATH" ]] || sudo mkdir -p "$CTF_PATH"

echo "[+] Copying all CTF code to destination folder"
sudo rsync -a "$CODE_PATH/" "$CTF_PATH/"

# This is because sync'ing files is done with unison
if [[ "$MODE" == "dev" ]]; then
	echo "[+] Setting permissions"
	sudo chmod -R 777 "$CTF_PATH/"
fi

# There we go!
source "$CTF_PATH/tools/lib.sh"

# Ascii art is always appreciated
set_motd "$CTF_PATH"

# Off to a good start...
sudo apt-get update
package emacs

# osquery, of course!
install_osquery

# Install MySQL
install_mysql "$P_ROOT"

# Install MyCLI
install_mycli

# Install git
package git

# Install HHVM
install_hhvm "$CTF_PATH"

# Install Composer
install_composer "$CTF_PATH"

# Make sure all apache is gone
#sudo service apache2 stop
#apt-get autoremove

# Install nginx
install_nginx "$CTF_PATH" "$MODE"

# Install unison 2.48.3
install_unison
log "Remember install the same version of unison (2.48.3) in your host machine"

# Database creation
import_empty_db "root" "$P_ROOT" "$DB" "$CTF_PATH" "$MODE"

# Make attachments folder world writable
sudo chmod 777 "$CTF_PATH/game/data/attachments"
sudo chmod 777 "$CTF_PATH/game/data/attachments/deleted"

log 'Facebook-CTF deployment is complete!'

exit 0
# kthxbai
