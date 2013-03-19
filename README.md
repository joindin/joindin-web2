# Joind.in

This is the source code for the next generation of the Joind.in website - a resource set up to allow
events to get real-time feedback from those attending. It also gives speakers a 
way to claim and track their presentations over time.

This version is the next generation version, providing a responsive cross-device site for screens of all devices

You can either install joind.in on an existing PHP platform, or use our vagrant setup.

## Quick start with Vagrant

You can set up a development virtual machine running joind.in by following these simple instructions.

1. Install requirements. (Note: these are not required by joind.in itself, but are required for this quick start guide.)
    - VirtualBox (https://www.virtualbox.org/) (versions 4.0 and 4.1 are currently supported)
    - Ruby (http://www.ruby-lang.org/)
    - Vagrant (http://vagrantup.com/)

1. Clone repository to any location and fetch required submodules (containing Puppet manifests).

        git clone https://github.com/joindin/responsive --recursive
        cd responsive
        
    -- or -- 

        git clone https://github.com/joindin/responsive && cd responsive
        git submodule init
        git submodule update
        
1. Add hostname to /etc/hosts.

        echo "127.0.0.1 joindin.local" | sudo tee -a /etc/hosts

1. Start the process by running Vagrant.

        vagrant up

1. Browse to the newly provisioned development copy of joind.in.

        open http://joindin.local:8008

*Notes:*

- HTTP and SSH ports on the VM are forwarded to localhost (22 -> 2222, 80 -> 8008)

- The responsive directory you cloned will be mounted inside the VM at `/vagrant`
- You can develop by editing the files you cloned in the IDE of you choice.in

- To stop the VM do one of the following:
 
     `vagrant suspend` if you plan on running it later
     
     `vagrant destroy` if you wish to delete the VM completely

- Also, when any of of the Puppet manifests change, it is a good idea to rerun them:

        vagrant provision


## Quick Start for existing platforms

1. Clone repository to any location

        git clone https://github.com/joindin/responsive
        cd responsive
        
1. Create a vhost entry for the site. The docroot should be `/web`.

        <VirtualHost *:80>
            ServerName joindin.local
    
            DocumentRoot "/home/exampleuser/www/responsive/web"
    
            <Directory "/home/exampleuser/www/responsive">
                Options FollowSymLinks
                AllowOverride All
            </Directory>
        </VirtualHost>

1. Add hostname to /etc/hosts.

        echo "127.0.0.1 joindin.local" | sudo tee -a /etc/hosts

1. Enjoy the site!

## Other Resources

* The main website http://joind.in
* Issues list: http://joindin.jira.com/ (good bug reports ALWAYS welcome!)
* CI Environment: lots of output and information about tests, deploys etc: http://jenkins.joind.in
* Community: We hang out on IRC, pop in with questions or comments! #joind.in on Freenode

See LICENSE file for license information for this software
(located in /doc/LICENSE)
