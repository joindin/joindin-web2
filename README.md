# Joind.in

This is the source code for the next generation of the Joind.in website - a resource set up to allow
events to get real-time feedback from those attending. It also gives speakers a 
way to claim and track their presentations over time.

This version is the next generation version, providing a responsive cross-device site for screens of all devices

You can either install joind.in on an existing PHP platform, or use our vagrant setup.

## Quick start with Vagrant

To get you going without much hassle we created a vagrant-setup. To use it [fork the joindin-vm](https://github.com/joindin/joindin-vm) repository and follow the instructions in there.

This VM will load all three Joind.in projects (joind.in, joindin-vm and joindin-web2). 

## Other Resources

* The main website http://joind.in
* Issues list: http://joindin.jira.com/ (good bug reports ALWAYS welcome!)
* CI Environment: lots of output and information about tests, deploys etc: http://jenkins.joind.in
* Community: We hang out on IRC, pop in with questions or comments! #joind.in on Freenode

## Global .gitignore

git has the capability to define a global gitignore file , which means you can 
set up rules on your machine to ignore everything you don't want to include in 
your commits. This works not only for this project, but for all your other
projects too.

You can define the gitignore file with a command that looks like this, where the 
last argument is the file that holds the patterns to ignore: 

    $ git config --global core.excludesfile ~/.gitignore_global

Octocat gives [a good starting point](https://gist.github.com/octocat/9257657) for what to include, but you can also ignore the files used by your editor:

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

For more info on ignoring files, [github has an excellent help page](https://help.github.com/articles/ignoring-files/).
