# Facebook CTF

Welcome to Facebook CTF platform

# How can I run my own CTF?

The Facebook CTF platform can be provisioned in development or production environments. Follow these steps to get your CTF up and running.

# Development 

Clone the repository and just run `vagrant up`. It will create a local virtual machine with Ubuntu 14.04 using Vagrant and Virtual Box as engine.
The provisioning script will install all necessary to your the platform locally, using self-signed certificates.
The credentials will be `admin`/`password` and the machine will be available on `https://10.10.10.5 by default.

If you are going to be developing outside of the Vagrant machine, you need to synchronize the files using unison (bi-directional rsync, over SSH).
Use the script `/tools/unison.sh` to do this:

`./unison.sh PATH_TO_facebook-ctf_FOLDER`

# Production

The target system needs to be Ubuntu 14.04. Clone the repository, for example in `/home/ubuntu/facebook-ctf`

`./provision.sh prod /home/ubuntu/facebook-ctf`

And be ready to provide the path for your SSL certificates csr and key files.