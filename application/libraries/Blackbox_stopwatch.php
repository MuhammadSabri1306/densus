<?php

class Blackbox_stopwatch
{
    private $resultFormat;
    private $case;
    private $currCase;
    private $autoDebug;

    /*
     * $config => {
     *    String format,
     *    Bool auto[?true],
     *    String case,
     *    String description
     * }
     */
    public function __construct($config = [])
    {
        $this->resultFormat = isset($config['format']) ? $config['format'] : '%H:%I:%S:%f';
        $this->autoDebug = isset($config['auto']) ? $config['auto'] : false;
        $this->case = [];

        if(isset($config['case'], $config['description'])) {    
            $this->create_case($config['case'], $config['description']);
        } elseif(isset($config['case'])) {
            $this->create_case($config['case']);
        }
    }

    public function set_format($formatStr)
    {
        $this->resultFormat = $formatStr;
    }

    public function use_case($caseName)
    {
        $this->currCase = $caseName;
    }

    public function create_case($caseName, $description = 'Blackbox Stopwatch')
    {
        $case = new stdClass();

        $case->startTime = null;
        $case->endTime = null;
        $case->description = $description;

        $this->case[$caseName] = $case;
        $this->use_case($caseName);
    }

    public function start()
    {
        if(!isset($this->case[$this->currCase])) {
            return false;
        }
        $case = $this->case[$this->currCase];
        $case->startTime = new DateTime();
        return true;
    }

    public function stop()
    {
        if(!isset($this->case[$this->currCase])) {
            return false;
        }
        $case = $this->case[$this->currCase];
        $case->endTime = new DateTime();

        if($this->autoDebug) {
            $this->print_interval();
        }

        return true;
    }

    public function get_interval()
    {
        if(!isset($this->case[$this->currCase])) {
            return null;
        }

        $case = $this->case[$this->currCase];
        if($case->startTime && $case->endTime) {
            return $case->startTime->diff($case->endTime);
        }

        return null;
    }

    public function print_interval($dieAfter = true)
    {
        $case = $this->case[$this->currCase];
        $interval = $this->get_interval();

        if($interval) {
            echo $case->description . '\n';
            echo $interval->format($this->resultFormat);
        } else {
            echo "The interval of case '$this->currCase' is NULL";
        }

        if($dieAfter) {
            exit('\n--------------------------\nExit by Blackbox Stopwatch');
        }
    }
}