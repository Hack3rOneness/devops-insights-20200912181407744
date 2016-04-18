# Facebook CTF

Welcome to Facebook CTF platform

# How can I run my own CTF?

The Facebook CTF platform can be provisioned in development or production environments.

# Development

```
git clone https://github.com/CaptureTheBox/facebook-ctf.git
cd facebook-ctf
vagrant up
```

This will create a local virtual machine with Ubuntu 14.04 using Vagrant and Virtual Box as the engine.
It will be located on `PATH_TO_CTF_FOLDER/.vagrant/machines`.
The provisioning script will install all necessary software to the platform locally, using self-signed certificates.
The credentials will be `admin`/`password` and the machine will be available on `https://10.10.10.5 by default.

If you want to ssh into the virtualbox, run:
```
vagrant ssh
```

If you are going to be developing outside of the Vagrant machine, you need to synchronize the files using unison (bi-directional rsync, over SSH).

```
./tools/unison.sh PATH_TO_CTF_FOLDER
```

# Production

The target system needs to be Ubuntu 14.04. Clone the repository, for example in `/home/ubuntu/facebook-ctf`

`./provision.sh prod /home/ubuntu/facebook-ctf`

And be ready to provide the path for your SSL certificates csr and key files.

# Mac OS X tools setup

First you need to have brew installed: http://brew.sh/.

```
brew cask install virtualbox
brew cask install vagrant
brew cask install vagrant-manager
brew install unison

```
