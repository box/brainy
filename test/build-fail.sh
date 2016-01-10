#!/usr/bin/env sh

# This will `exit 1` when the git repo hasn't had `make` run properly.

make parsers clean

DIFF=`git diff`
if [ -n "$DIFF" ]; then
    echo "DIFF!";
    echo "'make' has not properly been run before committing.";
    exit 1;
fi
