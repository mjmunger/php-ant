<?php

namespace PHPAnt\Core;

/**
 * Process Lock control
 *
 * Class is used to control whether or not a PHP script will start, stop, or restart based on given times and timeouts.
 **/

class ProcessLock
{

    /**
     *
     * runfile - this is where we store the run file in the file system. It
     * is best to store this in /var/run/[application], where the user that
     * executes the script is also the owner of that directory.
     *
     * @type string
     */

    public $runfile = '';

    /**
     * Timeout
     *
     * This is the number of seconds that is allowed to elapse before we
     * kill the process and delete the PID file. Defaults to 1 hour.
     *
     * @type integer
     **/

    public $timeout = 3600;

    /**
     * PID
     *
     * This is the PID number of this process. It is used to kill the process if it evers stalls or needs to be restarted
     * @type string
     **/

    public $pid = 0;

    /**
     * startTime
     *
     * Keeps the start time timestamp for timeout calculations
     **/

    public $startTime = 0;

    /**
     * Tells us whether or not the runfile exists
     *
     * @type string
     **/

    public $hasRunFile = false;

    /**
     * Tells us whether or not the process is running.
     *
     * @type boolean
     **/

    public $running = false;

    /**
     * The file handle for the run file.
     **/

    public $fh = '';

    /**
     * Instantiates a class of the plc
     * @param string $runfile - the full path to where we should stick the run file.
     * @param integer $timeout - the number of seconds before we should restart the script. (Defaults - 3600)
     * @return void
     **/

    public function __construct($runfile,$timeout=3600) {
        $this->runfile = $runfile;
        $this->timeout = $timeout;

        if(file_exists($this->runfile)) {
            /**
             * Let's get the contents of the file to make our decision.
             *
             * The format of the file is [PID]|[UNIX TIMESTAMP], so we can parse this
             * to get the duration the script has been active as well as the process ID
             * to kill if it has hung or crashed.
             **/

            $buffer = file_get_contents($this->runfile);

            //Determine how long this script has been running by getting the second
            //value of the script and comparing it to "now"

            $parts = explode("|", $buffer);
            $this->pid = $parts[0];
            $this->startTime = $parts[1];
            $this->hasRunFile = true;
        }

        if(file_exists(dirname($this->runfile)) == false) mkdir(dirname($this->runfile),0777,true);

        /* Can we get a lock on this file? */
        $this->fh = fopen($this->runfile,'w');

        /* Attempt to get a lock. If we can get a lock, the process file is stale. If not, it is active / fresh. */

        $this->running = !flock($this->fh, LOCK_EX | LOCK_NB);
    }

    /**
     * Removes the run file so we can have a clean start up next time.
     *
     * @ return boolean
     **/

    public function done() {
        //If it's not a file, then bail out.
        if(is_file($this->runfile) == false) return

        //There IS a file! Do stuff!
        fflush($this->fh);            // flush output before releasing the lock
        flock($this->fh, LOCK_UN);    // release the lock
        fclose($this->fh);				//Close the file
        $r = unlink($this->runfile);
        return $r;
    }
}