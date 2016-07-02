<?php

namespace PHPAnt\Core;

/**
 * Represents a CLI Command
 */

/**
 * This class parses and recognizes CLI commands,and also provides
 * functionality for dealing with those commands.
 *
 * @package      BFW
 * @subpackage   Core
 * @category     CLI
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 

class Command {


  /**
  * @var string $raw_command The raw command as it was entered (less the lf) 
  **/
  
  var $raw_command = NULL;

  /**
  * @var string $full_command The entire command as it is read from readline. 
  **/
  
	var $full_command = NULL;

  /**
  * @var int $verbosity The verbosity level for the command as passed in from the CLI. 
  **/
  
	var $verbosity = 0;

  /**
  * @var boolean $match_status Tells whether or not a command was able to be matched to a function / method. 
  **/
  
  var $match_status = false;


  /**
  * @var int $length The length of the command. 
  **/
  
  var $length = 0;


  /**
  * @var array $tokens Tokens of a command (individual words) 
  **/

  var $tokens = NULL;
  
  /**
   * Instantiates a class of Command.
   * Example:
   *
   * <code>
   * $cmd = new Command($line)
   * </code>
   *
   * @return void
   * @param string $line the command from readline.
   * @author Michael Munger <michael@highpoweredhelp.com>
   **/

	function __construct($line) {
    $this->raw_command = trim($line);
		$this->full_command = strtolower($this->raw_command);
    $this->length = strlen($this->full_command);
    /* We use the raw commands here to allow values to be case sensitive when saving config values.*/
    $this->tokens = explode(' ',$this->raw_command);
	}

  /**
   * Checks to see if a command "starts with" a phrase or string.
   * Example:
   *
   * <code>
   * if($cmd->startsWith("set debug")) {
   *     //do something.
   * }
   * </code>
   *
   * @return boolean True if the pattern exists at the beginning of the string, false otherwise.
   * @param string $pattern The string to compare the command to.
   * @author Michael Munger <michael@highpoweredhelp.com>
   **/

	function startsWith($pattern) {
		$haystack = $this->full_command;
		return $pattern === "" || strrpos($haystack, $pattern, -strlen($haystack)) !== FALSE;
	}

  /**
   * Returns true if the string is found in the command
   * Example:
   *
   * <code>
   * if($C->contains('test string')) echo "Yes!";
   * </code>
   *
   * @return boolean true if it is there, false otherwise.
   * @param string $pattern the thing to look for.
   * @author Michael Munger <michael@highpoweredhelp.com>
   **/
  function contains($pattern) {
    return (stripos($this->raw_command, $pattern) !== false?true:false);
  }

  /**
   * Checks to see if a command "ends with" a phrase or string.
   * Example:
   *
   * <code>
   * if($cmd->endsWith("on")) {
   *     // do something.
   * }
   * </code>
   *
   * @return boolean True if the pattern exists at the end of the command, false otherwise.
   * @param string $pattern the pattern to check the command against.
   * @author Michael Munger <michael@highpoweredhelp.com>
   **/

	function endsWith($pattern) {
		$haystack = $this->full_command;
    // search forward starting from end minus pattern length characters
    $pointer = strlen($haystack) - strlen($pattern);
    $buffer = substr($haystack,$pointer,strlen($pattern));
    return stripos($haystack, $pattern, $pointer) !== FALSE;
  	}

    /**
     * Gets the last string of a command.
     * Example:
     *
     * <code>
     * if($cmd->startsWith("set debug")) {
     *     $lastToken = $cmd->getLastToken();
     *     if($lastToken == "on") {
     *         //do something
     *     } elseif ($lastToken = "off") {
     *        //do something else
     *     } else {
     *        echo "Command not recognized.\n";
     *     }
     * }
     * </code>
     *
     * @return string The last string in the command.
     * @param none
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

  	function getLastToken() {
      $buffer = explode(' ',$this->full_command);
      return trim($buffer[sizeof($buffer)-1]);
    }

    /**
     * Gets a token at a given position
     * Example:
     *
     * <code>
     * Example Code
     * </code>
     *
     * @return string The value of the token at that position.
     * @param integer $position the integer value of the position we are expecting to have a token.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function getToken($position) {
      $buffer = explode(' ',$this->full_command);
      return trim($buffer[$position]);
    }

    /**
     * Compares a command to a specified string.
     * Example:
     *
     * <code>
     * if($cmd->is("exit")) {
     *     exit;
     * }
     * </code>
     *
     * @return boolean True if the command matches the pattern. False otherwise.
     * @param string $pattern The string to which we will compare the command.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function is($pattern) {
      $pattern = trim($pattern);
      $command = trim($this->full_command);

      $same = ($command == $pattern?true:false);

      if($this->verbosity > 9) {
        printf("%s == %s ?",$this->full_command,$pattern);
        print(($same?"YES":"NO"));
      }

      return $same;
    }

    /**
     * Removes the provided, beginning commands from the command to get a multi-token parameter
     * Example:
     *
     * <code>
     * $fullName = $cmd->leftStrip('name get');
     * echo $fullName;
     * //Output: "Michael Munger"
     * </code>
     *
     * @return string
     * @param string $strip The portion of the command you want to strip from the left side of the full command.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function leftStrip($strip) {
      $strip   = explode(" ", $strip);
  		$command = explode(' ',$this->full_command);
      $buffer  = array_diff($command, $strip);
      $return  = implode(' ', $buffer);

      return trim($return);
    }

    function splitOn($string) {
      $buffer = explode($string, $this->full_command);
      $wanted = trim($buffer[1]);
      return $wanted;
    }

    /**
     * Dumps debugging information to the CLI about the command being processed.
     * Example:
     *
     * <code>
     * $cmd->CLIDump();
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function CLIDump() {
      str_pad('', 20,'=');
      echo PHP_EOL;
      echo "Arguments have been passed:";
      echo PHP_EOL;

      echo str_pad('Command', 20);
      echo $this->command;
      echo PHP_EOL;

      echo str_pad('Verbosity', 20);
      echo $this->verbosity;
      echo PHP_EOL;  

      echo str_pad("Match Status",20);
      echo $this->match_status;
      echo PHP_EOL;

      echo str_pad("Length",20);
      echo $this->length;
      echo PHP_EOL;
      
      str_pad('', 20,'=');
      echo PHP_EOL;      
    }
}
?>