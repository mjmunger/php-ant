#!/bin/bash
OUTPUTDIR=/home/`whoami`/www/rtfm/

rm -fvr $OUTPUTDIR

phpdoc \
-f application_top.php \
-f application_local.php \
-d classes \
-d children \
-d plugins \
-t $OUTPUTDIR
