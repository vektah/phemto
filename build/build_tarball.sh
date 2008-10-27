#!/bin/sh

# Builds project release.
#
cd ../../
NAME=phemto_`cat phemto/VERSION`.tar.gz
tar zcf $NAME phemto --exclude-vcs
