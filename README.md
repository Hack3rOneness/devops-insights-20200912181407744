# Facebook CTF

Welcome to Facebook CTF platform.

TODO: Add screenshot.

## Development

### Prerequisites

First install the required tools. Note that in the following instructions, installing Unison is optional. If you prefer to do all of your development on the virtual machine itself, it is not necessary.

#### OS X

First you need to have brew installed: http://brew.sh/.

```
brew cask install virtualbox
brew cask install vagrant
brew cask install vagrant-manager
brew install unison
```

#### Linux

TODO

### Development setup

```
git clone https://github.com/CaptureTheBox/facebook-ctf.git
cd facebook-ctf
vagrant up
```

This will create a local virtual machine with Ubuntu 14.04 using Vagrant and VirtualBox as the engine. The provisioning script will install all necessary software to the platform locally, using self-signed certificates. The credentials will be `admin`/`password` and the machine will be available on `https://10.10.10.5 by default.

If you want to ssh into the virtualbox, run:
```
vagrant ssh
```

If you are going to be developing outside of the Vagrant machine, you need to synchronize the files using Unison (bi-directional rsync, over SSH).

```
./extra/unison.sh PATH_TO_CTF_FOLDER
```

## Production

The target system needs to be Ubuntu 14.04. Clone the repository, for example in `/home/ubuntu/facebook-ctf`

`./extra/provision.sh prod /home/ubuntu/facebook-ctf`

And be ready to provide the path for your SSL certificates csr and key files.

## Troubleshooting

### `vendor` folder wasn't created when running `./extra/provisioning.sh`

That might be because locale is not set correctly. Try running the command below first:
```
export LC_ALL=C
```
