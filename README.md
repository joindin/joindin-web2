# Joind.in

This is the source code for the next generation of the Joind.in website - a resource set up to allow
events to get real-time feedback from those attending. It also gives speakers a 
way to claim and track their presentations over time.

This version is the next generation version, providing a responsive cross-device site for screens of all devices

You can either install joind.in on an existing PHP platform, or use our vagrant setup.

## Quick start with Vagrant

The virtual machine has been moved to a different repo. To use it [fork the joindin-vm](https://github.com/joindin/joindin-vm) repository and follow the instructions in there. 

This VM will load all three Joind.in projects (joind.in, joindin-vm and joindin-web2). 

## Quick Start for existing platforms

1. Clone repository to any location

        git clone https://github.com/joindin/joindin-web2
        cd joindin-web2
        
1. Create a vhost entry for the site. The docroot should be `/web`.

        <VirtualHost *:80>
            ServerName joindin.local
    
            DocumentRoot "/home/exampleuser/www/joindin-web2/web"
    
            <Directory "/home/exampleuser/www/joindin-web2">
                Options FollowSymLinks
                AllowOverride All
            </Directory>
        </VirtualHost>

1. Add hostname to /etc/hosts.

        echo "127.0.0.1 joindin.local" | sudo tee -a /etc/hosts

1. Enjoy the site!

1. Set up Redis: instructions can be found at http://redis.io/topics/quickstart but to be honest, your favourite package manager should do a good enough job

1. Enjoy the site!

## Configuration

1. Copy the file config/config.php.dist to config/config.php

        cp config/config.php.dist config/config.php

1. Change the value of `apiUrl` to the URL of your development API if you don't want to use the production API.

       Note that if you are connecting to the production API, you will find that you won't be able to log in as you don't have the correct oauth client_id.

       **Create a local copy of the API from the [GitHub project](https://github.com/joindin/joindin-api) and then you can log in to it from your web2 installation**
 

## Other Resources

* The main website http://joind.in
* Issues list: http://joindin.jira.com/ (good bug reports ALWAYS welcome!)
* CI Environment: lots of output and information about tests, deploys etc: http://jenkins.joind.in
* Community: We hang out on IRC, pop in with questions or comments! #joind.in on Freenode

## Global .gitignore

git has the capability to define a global gitignore, which means you can set a local file to ignore everything you don't 
want to be commited by accident. This is not only recommended for this project, but for all your projects.

You can define this gitignore file with the following command. The gitignore is expected to be in `~/.gitignore_global`.
Of course you can use every filename you want.  

    $ git config --global core.excludesfile ~/.gitignore_global

Octocat gives [a good starting point](https://gist.github.com/octocat/9257657) to what to put in.

Beside this, we recommend to add meta data to all the IDEs:

    # Eclipse
    .classpath
    .project
    .settings/
    
    # Intellij
    .idea/
    *.iml
    *.iws
        
    # Maven
    log/
    target/

    # Netbeans
    nbproject/private/

More on ignoring files, [can be find on github](https://help.github.com/articles/ignoring-files/).
