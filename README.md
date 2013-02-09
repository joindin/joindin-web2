# Joind.in

This is the source code for the next generation of the Joind.in website - a resource set up to allow
events to get real-time feedback from those attending. It also gives speakers a 
way to claim and track their presentations over time.

This version is the next generation version, providing a responsive cross-device site for screens of all devices

You can install joind.in on an existing PHP platform using the following instructions

## Quick Start

1. Create a vhost entry for the site. The docroot should be `/web`.

    <VirtualHost *:80>
        ServerName joindin.local

        DocumentRoot "/home/exampleuser/www/joind.in/web"

        <Directory "/home/exampleuser/www/joind.in">
            Options FollowSymLinks
            AllowOverride All
        </Directory>
    </VirtualHost>

2. Add hostname to /etc/hosts.

   echo "127.0.0.1 joindin.local" | sudo tee -a /etc/hosts

3. Enjoy the site!

## Other Resources

* The main website http://joind.in
* Issues list: http://joindin.jira.com/ (good bug reports ALWAYS welcome!)
* CI Environment: lots of output and information about tests, deploys etc: http://jenkins.joind.in
* Community: We hang out on IRC, pop in with questions or comments! #joind.in on Freenode

See LICENSE file for license information for this software
(located in /doc/LICENSE)
