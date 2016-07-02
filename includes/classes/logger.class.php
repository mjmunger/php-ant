<?php
/**
* Logger creates intelligent, granular log files so you can see what your code is doing and pinpoint failures.
*
* @author Michael Munger <michael@highpoweredhelp.com>
**/

/**
* PHP Version 5.4.9+
* 
* @copyright 2013-2015 High Powered Help, Inc. (http://www.highpoweredhelp.com)
* @license MIT License (MIT)
* @link https://bugzy.highpoweredhelp.com
*
* Copyright (c) 2013-2015 High Powered Help, Inc. (http://www.highpoweredhelp.com)
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
**/


class Logger
{
    /**
    * @var string $logfile
    * 
    * The log file you want this class to create and log to.
    *
    **/
    
    private $logfile = '';
    
    /**
    * @var string $handle
    *
    * The filepointer to the open log file. Public visibility allows you to pass a parent logger object / parent class' file handle for its logger object. So, all messages appear in one file.
    **/
    
    public $handle = '';
    
    /**
    * @var string $logPath
    * 
    * The path to the log file in the file system.
    **/
    
    private $logPath = '';
    
    /**
    * @var string $mode
    *
    * The filemode the logger uses. (Defaults to 'a' - append)
    **/
    
    private $mode = '';
    
    /**
    * @var string label
    *
    * First line label that appears after the timestamp.
    **/
    public $label = '';

    /**
    * @var boolean skipMessages
    *
    * Determines whether or not startup and shutdown messages are logged from the __construct() and __destruct() methods.
    **/
    
    public $skipMessages = false;

    /**
    * @var int $verbosity
    *
    * Controls the verbosity of the logging output. Should be a range of 0-10 where 0 is off, 1 is critical, 2 is warning, 3 is info, 4+ is debug, and 10 is absolutely everything. 
    * <code>
    * if($some_object->logger->verbosity > 4) {
    *   debug_print($something.very.technical);
    * }
    * </code>
    **/

    public $verbosity = 0;

    /** 
    * Instantiate a logger class object
    *
    * @param string $label Required. The label that appears after the timestamp. (Used for filtering).
    * @param string $filename Optional. If specified, the logger will use this filename to store the log. Defaults to the name of the $label.log.
    * @param string $path Optional. The location where you want the log file stored. Defaults to ~/log/
    * @param string $mode Optional. The write mode of the file. Defaults to append ('a').
    * @param string $skipMessages Optional. When set to true, this skips the class startup and shutdown messages. Default shows these messages so you can see when one class is starting up and shutting down (false).
    *
    * @return void
    **/
    
    function __construct($label, $filename = '', $path='',$skipMessages=false,$mode='a+')
    {
        //set_error_handler('self::throw_error');
        $this->label = $label;

        if($filename) {
            $this->logfile = $filename;
        } else {
            $this->logfile = $label.'.log';
        }

        if($path) {
            $this->logPath = $path;
        } else {
            $current_user = trim(shell_exec('whoami'));
            $this->logPath = '/home/'.$current_user.'/log/';
        }

        $this->skipMessages = $skipMessages;

        $this->mode = $mode;
        //If the directory doesn't exist, the try to make it.
        if(!(file_exists($this->logPath))) {
            mkdir($this->logPath,0777,true);
        }
        
        $this->handle = fopen($this->logPath.$this->logfile,$this->mode);
        
        if(!$this->skipMessages) {
            $this->log(sprintf("%s starting up...",$this->label));
        }
    }

    
    /**
    * Logs a message to the log file.
    * 
    * @param string $message The data / message you want sent to the log file.
    * @return void
    **/
    
    function log($message)
    {
        $format = "%s\t%s\t%s\n";
        fwrite($this->handle,sprintf($format,date("Y-m-d H:i:s"),$this->label,$message));
    }

    function __destruct() {
        /*fclose($this->handle);*/
    }
}