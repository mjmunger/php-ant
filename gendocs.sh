#!/bin/bash
# Generates php docs for PHP-ant and uploads them to https://php-ant.org/rtfm
rm -vfr rtfm/

phpdoc -t rtfm/ \
 -f ./includes/UserAuthenticatorInterface.php \
 -f ./includes/bootstrap.php \
 -f ./includes/functions.php \
 -f ./includes/interfaces.php \
 -f ./includes/classes/hook.class.php \
 -f ./includes/classes/UserAuthenticator.class.php \
 -f ./includes/classes/ConfigWeb.class.php \
 -f ./includes/classes/Users.class.php \
 -f ./includes/classes/SecurityNonce.class.php \
 -f ./includes/classes/Settings.class.php \
 -f ./includes/classes/Cli.class.php \
 -f ./includes/classes/WebEnvironment.class.php \
 -f ./includes/classes/h5-select.class.php \
 -f ./includes/classes/ScriptExecution.class.php \
 -f ./includes/classes/ConfigBase.class.php \
 -f ./includes/classes/PHPAntSigner.class.php \
 -f ./includes/classes/HTTPEnvironment.class.php \
 -f ./includes/classes/UsersRoles.class.php \
 -f ./includes/classes/WebRequest.class.php \
 -f ./includes/classes/TableLog.class.php \
 -f ./includes/classes/AppBlacklist.class.php \
 -f ./includes/classes/Logger.class.php \
 -f ./includes/classes/Execution.class.php \
 -f ./includes/classes/ConfigFactory.class.php \
 -f ./includes/classes/SSLEnvironment.class.php \
 -f ./includes/classes/acl.class.php \
 -f ./includes/classes/ConfigCLI.class.php \
 -f ./includes/classes/ServerEnvironment.class.php \
 -f ./includes/classes/emaillog.class.php \
 -f ./includes/classes/Command.class.php \
 -f ./includes/classes/Notif.class.php \
 -f ./includes/classes/PermissionManager.class.php \
 -f ./includes/classes/Acl.class.php \
 -f ./includes/classes/AndErrorHandler.class.php \
 -f ./includes/classes/PHPAntSignerFile.class.php \
 -f ./includes/classes/AntApp.class.php \
 -f ./includes/classes/CLISetup.class.php \
 -f ./includes/classes/userrole.class.php \
 -f ./includes/AppEngine.php \
 -f ./cli-setup.php \
 -f ./php-ant-signer.php \
 -f ./setup.php \
 -f ./status.php \
 -f ./ajax.php \
 -f ./cli.php \
 -f ./index.php
 
rsync -avh --delete --progress rtfm/ phpant:/home/phpant/www/rtfm
