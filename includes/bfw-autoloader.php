<?php
/**
 * Autoloader for BFW Toolkit classes.
 *
 * This function receives the name of a class to load, and then looks in the following places for it:
 * includes/parents/
 * includes/children/
 * includes/classes
 *
 * It EXPECTS that a parent class have an 's' at the end of the name of the
 * class. For example, the database table 'users' should generate a a parent
 * class of 'users', which represents the fact that we are talking about "all
 * users", but the child class, which is used when we are manipulating an
 * individual user,  will be named 'user'. Since we NEVER instantiate a parent
 * class, but always instantiate child classes, an 's' is added to the name of
 * the class, which has been instantiated, when we attempt to load the class.
 *
 * Example:
 * when we call:
 * <code>
 * $u = new User();
 * </code>
 * The autoloader does the follwowing:
 * 1. Convert the class to all-lower-case: "User" -> "user"
 * 2. Add an "s", and look for the parent: does "includes/parents/users.class.parent.php" exist? If yes, load it.
 * 3. Look for the child class: does "includes/children/user.class.parent.php" exist? If yes, load it.
 * 4. Does this class exist as a utility class? (One that was not generated
 *    with db2class to manipulate database data - these are stored in
 *    includes/classes/). Ergo, does "includes/classes/user.class.php" exist? IF
 *    yes, load it.
 *
 * The exact function of this library is to generate candidate file paths,
 * which MIGHT exist, and load them into the $candidate_files array. Then,
 * finally, loop through that array and load all candidate files that exist in
 * FIFO order (parents before children before utility classes)
 *
 * @return void
 * @param string $class the name of the class to load
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/

function bfw_autoloader($class) {
    $class = strtolower($class);
    $candidate_files = array();
    
    /* Attempt to load the parent class first. Child classes always extend parent classes. */
    //$candidate_path = sprintf('includes/parents/%s.class.parent.php',$class);
    //array_push($candidate_files, $candidate_path);

    /* Attempt to load the child class second after parent class dependencies have been satisfied. */
    //$candidate_path = sprintf('includes/children/%s.class.php',$class);
    //array_push($candidate_files, $candidate_path);

    /* If this is not a database abstraction, then it is located in the classes directory. Try that last. */
    $candidate_path = sprintf('includes/classes/%s.class.php',$class);
    array_push($candidate_files, $candidate_path);

    /* Loop through all candidate files, and attempt to load them all in the correct order (FIFO) */
    foreach($candidate_files as $dependency) {
        echo "<pre>"; echo "Looking for: $dependency"; echo "</pre>";
        if(file_exists($dependency)) {
            if(is_readable($dependency)) {
                echo "<pre>"; echo "Found: $dependency"; echo "</pre>";
                include($dependency);
            }
        }
    }
}

/* REGISTER THE AUTOLOADER! This has to be done first thing! */
spl_autoload_register('bfw_autoloader');