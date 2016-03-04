# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.network "private_network", ip: "10.10.10.5"
  config.vm.hostname = "facebookCTF-Dev"
  config.ssh.shell = "bash -c 'BASH_ENV=/etc/profile exec bash'"
  config.vm.synced_folder ".", "/var/www/facebook-ctf", :mount_options => ["dmode=777", "fmode=666"], :create => true
  config.vm.provision "shell", path: "tools/provision_dev.sh"
end
