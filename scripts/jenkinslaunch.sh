#!/bin/bash

if [ -z $TARGETBASE ]
then
	echo "Please specify TARGETBASE in the environment, eg /var/www/joind.in"
	exit 1
fi
#TARGETBASE=/var/www/joind.in

TARGET=${TARGETBASE}/${BUILD_NUMBER}
export TARGET


if [ -z $GITHUB_REPO ]
then
	GITHUB_REPO=joindin-web2
fi

if [ -z $GITHUB_USER ]
then
	GITHUB_USER=joindin
fi

if [ -z $BRANCH ]
then
	BRANCH=master
fi
LAUNCHREF=remotes/deployremote/$BRANCH

sg web -c "
mkdir -p $TARGET \
 ; git remote set-url deployremote https://github.com/$GITHUB_USER/$GITHUB_REPO.git \
&& git fetch deployremote \
&& git archive $LAUNCHREF | tar xC $TARGET \
&& (echo $TARGET ; echo $LAUNCHREF) > $TARGET/web/release.txt \
&& ln -s $TARGETBASE/config.php $TARGET/config/config.php \
&& composer install -o --prefer-dist --no-dev --no-progress --working-dir=$TARGET \
&& ln -s $TARGET $TARGETBASE/www.new \
&& mv -Tf $TARGETBASE/www.new $TARGETBASE/www \
&& rm -rf /tmp/joindin-twig-cache/live \
&& rm -rf /tmp/joindin-twig-cache/test
"
