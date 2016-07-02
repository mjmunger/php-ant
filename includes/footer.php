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

$PE->runActions('end-container');

?>
</div><!-- End Container -->
<footer>

<?php $PE->runActions('footer-open'); ?>

<?php $PE->runActions('footer-close'); ?>

</footer>


<?php $PE->runActions('footer-js-inject'); ?>
</body>
</html>