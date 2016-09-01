 #!/bin/bash
 # Download the current STABLE version of PHP to /usr/src/php-src/
 # Copy this script to that directory, and use it to configure and compile PHP.
 #
 # NOTICE: If one of the package below is not available (name change, for example) apt-get
 # will not run properly, and you'll end up with errors. If you find a package that is needed
 # but not listed in this apt-get statement, please add it, and contribute back by sending
 # a note to michael@highpoweredhelp.com listing your distribution and the package that was
 # not included, but needed.
 #

 function print_usage {
 	echo "SUMMARY:"
 	echo ""
 	echo "This script will install needed dependencies for a Debian based system,"
 	echo "then configure and install PHP. Once complete, it will notify you via"
 	echo "email that the configuration is complete."
 	echo ""
 	echo "USAGE:"
 	echo ""
 	echo "./config-compile.sh [youremail@yourdomain.com]"
 	echo ""

 }

 if [ "$#" == "0" ]; then
     print_usage
     exit 1
fi

echo "Compiling PHP for `hostname` and sending a notification to $1"

 apt-get install --assume-yes apache2-dev libxml2-dev libbz2-dev libreadline-dev libmcrypt-dev libssl-dev libcurl4-openssl-dev libfreetype6-dev
 # Sometimes, needed for Ubuntu
 apt-get install --assume-yes libjpeg8-dev libpng12-dev
 make clean
 ./buildconf
 ./configure --with-config-file-path=/etc/php5/apache2 \
 --with-pear=/usr/share/php \
 --with-bz2 \
 --with-curl \
 --with-gd \
 --enable-calendar \
 --enable-mbstring \
 --enable-bcmath \
 --enable-sockets \
 --with-libxml-dir \
 --with-mysqli \
 --with-mysql \
 --with-mysql-sock=/var/run/mysqld/mysqld.sock \
 --with-pdo-mysql \
 --with-openssl \
 --with-ssl-lib=/usr/lib/i386-linux-gnu/ \
 --with-regex=php \
 --with-readline \
 --with-zlib \
 --with-libzip \
 --enable-zip \
 --with-apxs2=/usr/bin/apxs2 \
 --enable-soap \
 --with-freetype-dir=/usr/include/freetype2/ \
 --with-freetype \
 --with-mcrypt=/usr/src/mcrypt-2.6.8 \
 --with-jpeg-dir=/usr/lib/x86_64-linux-gnu/ \
 --with-png-dir=/usr/lib/x86_64-linux-gnu/
 make
 make tests
 make install
 
 cat body | mailx $1 -s "`hostname` PHP Compile done"
