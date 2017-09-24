<?php 
/**
 * System Footer
 *
 * Closes the main container div, contains the footer, and also contains the
 * hooks: footer-open and footer-close.
 *
 * @package      BFW Toolkit
 * @subpackage   Core
 * @category     Page Components
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 

$Engine->runActions('end-container');

?>
</div><!-- End Container -->
<footer>

<?php $Engine->runActions('footer-open'); ?>

<?php $Engine->runActions('footer-close'); ?>

</footer>


<?php $Engine->runActions('footer-js-inject'); ?>
</body>
</html>