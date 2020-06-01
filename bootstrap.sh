#!/bin/sh
if test $# -lt 1
then
    echo Usage: bootstrap.sh who
    echo    eg: bootstrap.sh test 
    exit
fi

REGION=$1

DIRS="logs"

ROOT=`pwd`

echo create application environment for $REGION

cd $ROOT

ln -sf  YConfig.php.$REGION config/YConfig.php
echo "link created: ln -sf config/YConfig.php.$REGION config/YConfig.php "
for dir in $DIRS
do
    if (test ! -d $dir)
    then
        mkdir -p $dir
        chmod 777 $dir
        echo mkdir $dir ................ OK
    fi
done
