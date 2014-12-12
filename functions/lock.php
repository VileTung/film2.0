<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Locks a session, also makes it stoppable
 */

class locker
{
    private $session;

    //Constructor
    public function __construct($process)
    {
        global $cache;

        //Generate session ID
        $this->session = $session = mt_rand(10000, 65535);

        //Create lock file
        $sessionFile = fopen($cache . "lock_" . $session, "w");
        fclose($sessionFile);

        //Make sure everything went OK
        if (!file_exists($cache . "lock_" . $session))
        {
            throw new Exception("Couldn't create a lock!");
        }

        //Database connection
        Database();

        sqlQueryi("INSERT INTO `sessions` (`process`,`sessionId`,`progress`,`start`,`state`) VALUES (?,?,?,?,?)", array(
            "sisss",
            $process,
            $session,
            "0",
            date("Y-m-d H:i:s"),
            "Working"));
    }

    public function update($progress)
    {
        sqlQueryi("UPDATE `sessions` SET `progress` = ? WHERE `sessionId` = ?", array(
            "si",
            round($progress),
            $this->session));
    }

    //Get session ID, don't know why..
    public function getSession()
    {
        return $this->session;
    }

    //Check if session.lock still exists, otherwise, exit!
    public function check()
    {
        global $logging, $cache;

        if (!file_exists($cache . "lock_" . $this->session))
        {
            //Update state
            sqlQueryi("UPDATE `sessions` SET `state` = ?, `end` = ? WHERE `sessionId` = ?", array(
                "ssi",
                "Aborted",
                date("Y-m-d H:i:s"),
                $this->session));

            throw new Exception("Stopping, " . $this->session . ".lock doesn't exist");
        }
        else
        {
            //Message
            $logging->debug("Process continues (" . $this->session . ")");
        }
    }

    //Regular exit
    public function stop()
    {
        global $logging, $cache;

        unlink($cache . "lock_" . $this->session);

        //Update state
        sqlQueryi("UPDATE `sessions` SET `state` = ?, `end` = ? WHERE `sessionId` = ?", array(
            "ssi",
            "Finished",
            date("Y-m-d H:i:s"),
            $this->session));

        //Message
        $logging->info("Process stopped (" . $this->session . ")");
    }
}

?>