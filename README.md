
## What is FBCTF?

The Facebook CTF is a platform to host “Jeopardy” and “King of the Hill” style Capture the Flag competitions. 

## How do I use FBCTF?

* Organize a competition. This can be with as few as two participants, all the way up to several hundred. The participants can be physically present, virtually active, or a combination of the two. 
* Follow setup instructions below to spin up platform infrastructure. 
* Enter challenges into Admin Panel 
* Have participants register as teams
    * If a closed competition: 
        * In Admin Portal, generate and export tokens to be shared with approved teams, then point participants towards hosted webpage
    * If  an open competition: 
        * Point participants towards hosted webpage
* Enjoy!

# Installation 

The Facebook CTF platform can be provisioned in development or production environments.

## Requirements 

* Production
    * Hardware 
        * VM or physical machine running Linux 14.04 LTS
    * Software and accounts
        * Git 
        * GitHub Account
* Development 
    * Hardware
        * VM or physical machine capable of running Linux or OSX
    * Software and accounts 
        * Virtual Box (https://www.virtualbox.org/wiki/Downloads)
        * Vagrant (https://www.vagrantup.com/downloads.html)
        * Brew (http://brew.sh/) (required only for OS X)
        * Github Account
    * Required installations for SSH’ing and developing outside of the Vagrant Machine
        * Vagrant-manager (http://vagrantmanager.com/downloads/) 
        * Unison (https://www.cis.upenn.edu/~bcpierce/unison/download.html) 

## Production

The target system needs to be Ubuntu 14.04. After installing git, clone the repository. For example, in `/home/ubuntu/facebook-ctf`
Then, push the following command. 
`./extra/provision.sh prod /home/ubuntu/facebook-ctf` 
Be ready to provide the path for your SSL certificates csr and key files.

## Development

    `git clone https://github.com/Facebook/FBCTF
     cd facebook-ctf
    vagrant up`



This will create a local virtual machine with Ubuntu 14.04 using Vagrant and Virtual Box as the engine. It will be located on PATH_TO_CTF_FOLDER/.vagrant/machines. The provisioning script will install all necessary software to the platform locally, using self-signed certificates. The credentials will be admin/password and the machine will be available on https://10.10.10.5 (https://10.10.10.5/) by default.

## Optional installation  

If you want to ssh into the virtualbox, run:

`vagrant ssh`

If you are going to be developing outside of the Vagrant machine, you need to synchronize the files using unison (bi-directional rsync, over SSH).

`./extra/unison.sh PATH_TO_CTF_FOLDER`


If you run into any blockers, please submit and issue on the GitHub repo. 

## Contribute 

You’ve used it, now you want to make it better? Awesome! Pull requests are welcome! 

## Who wrote it? 

FBCTF was created by Facebook’s Information Security team at its headquarters in Menlo Park, CA. 

## Have more questions? 


Check out the wiki pages attached to this repo. 

## License 

This source code is licensed under the Creative Commons Attribution-NonCommercial 4.0 International license found in the LICENSE file in the root directory of this source tree.


