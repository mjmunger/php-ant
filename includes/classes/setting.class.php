<?php
/**
 * Represents a setting in the BFW Toolkit ecosystem.
 */
 /**
 * Represents a setting in the BFW Toolkit ecosystem.   
 *
 * @package      BFW Toolkit
 * @subpackage   Core
 * @category     Settings
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 

class Setting extends settings
{
    /**
     * Prints the key value pair for this record in the CLI
     * Example:
     *
     * <code>
     * $s = new Setting();
     * $s->CLIPrintMe();
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
	function CLIPrintMe() {
        echo str_pad($this->settings_key, 20);
        echo $this->settings_value;
        echo PHP_EOL;
    }
}
?>
