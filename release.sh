#!/bin/sh

set -e

V=$1

if [ -z "$V" ]; then
    >&2 echo "Version cannot be empty"
    exit 1
fi

tagname=v$V
archive=$(mktemp)

current_version=`grep "plugin->version" version.php | sed -e 's/^.*=\s*\(.*\);$/\1/'`
new_version=`date +%Y%m%d00`
if [ $current_version -ge $new_version ]; then
    new_version=$(echo "$current_version + 1" | bc)
fi

sed -e "s/\(plugin->version\).*$/\1 = $new_version;/g" -i version.php
sed -e "s/\(plugin->release\).*$/\1 = '$tagname';/g" -i version.php

git add version.php

git commit -m "Release $tagname / $new_version"
git tag -m "Release $tagname" -s $tagname
