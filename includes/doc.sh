#!/bin/bash
OUTPUTDIR=/home/`whoami`/www/rtfm/

rm -fvr $OUTPUTDIR

phpdoc \
-f bootstrap.php \
-f application_local.php \
-f AppEngine.php \
-d classes \
-d classes \
-d apps \
-t $OUTPUTDIR
