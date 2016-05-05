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
sudo rsync -a --exclude node_modules --exclude vendor "$CODE_PATH/" "$CTF_PATH/"

# This is because sync'ing files is done with unison
if [[ "$MODE" == "dev" ]]; then
    echo "[+] Setting permissions"
    sudo chmod -R 777 "$CTF_PATH/"
fi

# There we go!
source "$CTF_PATH/extra/lib.sh"

# Ascii art is always appreciated
set_motd "$CTF_PATH"

# Off to a good start...
sudo apt-get update
package language-pack-en
package emacs

# osquery, of course!
install_osquery

# Install htop
package htop

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
composer.phar install

# Install NPM and grunt
package npm
package nodejs-legacy
npm install -g grunt
npm install

# Run grunt to generate JS files
grunt

# Install nginx
install_nginx "$CTF_PATH" "$MODE"

# Install unison 2.48.3
install_unison
log "Remember install the same version of unison (2.48.3) in your host machine"

# Database creation
import_empty_db "root" "$P_ROOT" "$DB" "$CTF_PATH" "$MODE"

# Make attachments folder world writable
sudo chmod 777 "$CTF_PATH/src/data/attachments"
sudo chmod 777 "$CTF_PATH/src/data/attachments/deleted"

log 'Facebook-CTF deployment is complete!'

exit 0
