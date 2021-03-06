#!/bin/bash
# Generates PHPDoc documentation for this PHP-Ant Installation.
rm -vfr "./docs"
phpdoc -d "./includes/" -t "./docs" \
--ignore="includes/vendor/*" \
--ignore="includes/libs/*" \
--ignore="includes/classes/pImage.class.php" \
--ignore="includes/classes/pScatter.class.php" \
--ignore="includes/classes/pStock.class.php" \
--ignore="includes/classes/pSpring.class.php" \
--ignore="includes/classes/pBarcode128.class.php" \
--ignore="includes/classes/pBubble.class.php" \
--ignore="includes/classes/pRadar.class.php" \
--ignore="includes/classes/pData.class.php" \
--ignore="includes/classes/pSurface.class.php" \
--ignore="includes/classes/pPie.class.php" \
--ignore="includes/classes/pBarcode39.class.php" \
--ignore="includes/classes/pIndicator.class.php" \
--ignore="includes/classes/pCache.class.php" \
--ignore="includes/classes/pSplit.class.php" \
--ignore="includes/apps/ant-app-default/tests/AntAppDefaultTest.php" \
--ignore="includes/apps/ant-app-test-app/tests/AntAppClassTest.php" \
--ignore="includes/apps/phpant-app-manager/tests/AntAppManagerTest.php" \
--ignore="includes/apps/ant-app-authenticator/tests/UsersAndRolesTest.php" \
--ignore="includes/apps/ant-app-authenticator/tests/APIAuthenticationTest.php" \
--ignore="includes/apps/ant-app-authenticator/tests/UserPassAuthenticationTest.php" \
--ignore="includes/apps/ant-app-authenticator/tests/RequestSelectorTest.php" \
--ignore="includes/apps/ant-app-authenticator/tests/KeyAuthenticationTest.php"