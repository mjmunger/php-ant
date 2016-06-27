#!/bin/bash
# Generates PHPDoc documentation for this PHP-Ant Installation.
rm -vfr "./docs"
phpdoc -d "." -t "./docs" \
--ignore="includes/classes/pImage.class.php" \
--ignore="includes/classes/pScatter.class.php" \
--ignore="includes/classes/pStock.class.php" \
--ignore="includes/classes/pSpring.class.php" \
--ignore="www/includes/classes/pBarcode128.class.php" \
--ignore="includes/classes/pBubble.class.php" \
--ignore="includes/classes/pRadar.class.php" \
--ignore="includes/classes/pData.class.php" \
--ignore="includes/classes/pSurface.class.php" \
--ignore="includes/classes/pPie.class.php" \
--ignore="includes/classes/pBarcode39.class.php" \
--ignore="includes/classes/pIndicator.class.php" \
--ignore="includes/classes/pCache.class.php" \
--ignore="includes/classes/pSplit.class.php" \
--ignore="includes/parents/*" \
--template="responsive-twig"