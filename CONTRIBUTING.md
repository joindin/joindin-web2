# CONTRIBUTING

## RESOURCES

If you wish to contribute to joind.in, please be sure to
read/subscribe to the following resources:

 -  Contributor Code of Conduct: https://github.com/joindin/joindin-web2/CODE_OF_CONDUCT.md
 -  Contributor's Guide:
    https://github.com/joindin/joind.in/wiki/How-to-Contribute-Code
 -  Contributor's mailing list:
    http://groups.google.com/group/joindin-developers
 -  Contributor's IRC channel:
    #joind.in on Freenode.net

## What to work on

You should work on what you want to. Our bug tracker is
here: https://joindin.jira.com

Any issues that have the "hackathon" or "easypick" label are ones that we think
are a good starting point. This [JIRA filter](https://joindin.jira.com/issues/?jql=project%20%3D%20JOINDIN%20AND%20labels%20in%20(hackathon%2C%20%22OR%22%2C%20easypick)) will give you the list of all
current issues with the "hackathon" or "easypick" issues.

If you have any problems, ask on the IRC channel or send an email to
the mailing list.

## Issue tracker management

If you start working on an issue, please assign yourself to it and mark the issue as "in progress".

## Linking branches to JIRA

If you include the JOINDIN-nnn issue number in the commit message, then JIRA will pick it up and display your branch on the issue.

## JIRA Smart Commits

If you want have your commit messages perform actions in JIRA. JIRA
has [documentation](https://confluence.atlassian.com/display/FISHEYE/Using+Smart+Commits)
on their extensive list of commands, but here is an example of what
you can use in your commit message:

```
JOINDIN-<number> #close <message>
```

An example would be:

```
JOINDIN-445 #close Fixed.
```

## PHP Version

The current PHP version for joind.in is PHP 5.6.

## Code Style

All PHP code follows [psr-2](http://www.php-fig.org/psr/psr-2/). One way to help ensure that your submitted code is PSR-2 is to run [PHP Code Sniffer]() against PSR-2 and the [.editorconfig](http://editorconfig.org/) plugin for your editor before submitting.
