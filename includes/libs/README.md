This directory is where we save third party libraries such as PHPMailer, etc... 

You can populate these libraries form the PHPAnt command line with the folling command:

    AntCLI*>libs git [url]

For example, to add the PHPMailer libs:

    AntCLI*>libs git https://github.com/PHPMailer/PHPMailer.git

*NOTE:*

It is not enough to simply populate these libraries into this directory. You must also have an app that responds to the load_loaders hook, which will register an SPL Autoloader for the library.
