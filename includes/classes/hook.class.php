<?php
/**
 * Represents a hook in the BFW Toolkit ecosystem.
 */

 /**
 *
 * This class is a simple representation of a hook. 
 *
 * @package      BFW Toolkit
 * @subpackage   Core
 * @category     Plugins
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */     

Class Hook {
 

    /**
    * @var string $hook The text hook used by the BFW Toolkit to trigger plugin firing. 
    **/
          
    var $hook      = NULL;

    /**
    * @var integer $priority The priority at which this plugin should be executed. Should be a value between 1 - 100. Default: 50. 
    **/
        
    var $priority  = NULL;

    /**
    * @var boolean $triggered Flag that tells us whether or not this plugin has been triggered yet or not. 
    **/
        
    var $triggered = NULL;

    /**
    * @var string $callback The callback function this hook will use to process data. 
    **/

    var $callback  = NULL;

    /**
    * @var array $arguments An array of arguments that the callback function may user. 
    **/
    
    var $arguments = NULL;

    /**
     * Instantiates a class of Hook
     * Example:
     *
     * <code>
     * $h = new Hook('cli-init',50,'declareMyself',array('foo','bar'));
     * </code>
     *
     * @return void
     * @param string $hook The text hook used by the BFW Toolkit to trigger plugin firing. 
     * @param integer $priority The priority at which this plugin should be executed. Should be a value between 1 - 100. Default: 50. 
     * @param string $callback The callback function this hook will use to process data. 
     * @param array $arguments An array of arguments that the callback function may user.      
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function __construct($hook, $priority, $callback, $arguments) {
        $this->hook      = $hook;
        $this->priority  = $priority;
        $this->triggered = NULL;
        $this->callback  = $callback;
        $this->arguments = $arguments;        
    }
}
?>





