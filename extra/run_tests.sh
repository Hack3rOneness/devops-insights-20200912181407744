#!/bin/bash
#
# Facebook CTF: script to start tests and code coverage
#
# Usage: ./run_tests.sh [path_to_code]
#

DB="fbctftests"
USER="ctf"
PWD="ctf"
CODE_PATH=${1:-/vagrant}

echo "[+] Starting tests setup in $CODE_PATH"

if ! [[ $(mysql_config_editor print --login-path=local) ]]; then
  mysql_config_editor set --login-path=local --host=localhost --user="root" --password
fi

mysql --login-path=local -e "CREATE DATABASE $DB;"
mysql --login-path=local -e "CREATE USER '$USER'@'localhost' IDENTIFIED BY '$PWD';"
mysql --login-path=local -e "GRANT ALL ON $DB.* TO '$USER'@'localhost';"
mysql --login-path=local -e "FLUSH PRIVILEGES;"
mysql --login-path=local "$DB" -e "source $CODE_PATH/database/test_schema.sql;"
mysql --login-path=local "$DB" -e "source $CODE_PATH/database/logos.sql;"
mysql --login-path=local "$DB" -e "source $CODE_PATH/database/countries.sql;"

echo "[+] DB Connection file"
cat "$CODE_PATH/extra/settings.ini.example" | sed "s/DATABASE/$DB/g" | sed "s/MYUSER/$USER/g" | sed "s/MYPWD/$PWD/g" > "$CODE_PATH/settings.ini"

echo "[+] Starting tests"
hhvm --config tests/server.ini vendor/phpunit/phpunit/phpunit --configuration tests/phpunit.xml tests

echo "[+] Deleting test database"
mysql --login-path=local -e "DROP DATABASE IF EXISTS $DB;"
mysql --login-path=local -e "DROP USER '$USER'@'localhost'";
mysql --login-path=local -e "FLUSH PRIVILEGES;"

exit 0

