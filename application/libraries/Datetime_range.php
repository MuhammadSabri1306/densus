<?php

class Datetime_range
{
    public $format;
    private $startDate;
    private $endDate;

    public function __construct()
    {
        $this->format = 'Y-m-d H:i:s';
    }

    private function get_result()
    {
        $startDate = $this->startDate->format($this->format);
        $endDate = $this->endDate->format($this->format);
        return [$startDate, $endDate];
    }

    public function get_by_year($year)
    {
        $this->startDate = new DateTime($year.'-01-01');
        $this->startDate->setTime(0, 0, 0);

        $this->endDate = new DateTime($year.'-12-31');
        $this->endDate->setTime(23, 59, 59);

        return $this->get_result();
    }

    public function get_by_month($month, $year)
    {
        $this->startDate = new DateTime("$year-$month-01");
        $this->startDate->setTime(0, 0, 0);

        $this->endDate = new DateTime("$year-$month-01");
        $this->endDate->modify('last day of this month');
        $this->endDate->setTime(23, 59, 59);

        return $this->get_result();
    }

    public function get_by_quarter($quarter, $year) {
        $quarter = (int) $quarter;
        $startmonth = (($quarter - 1) * 3) + 1;

        $this->startDate = new DateTime("$year-$startmonth-01");
        $this->startDate->modify('first day of this month');
        $this->startDate->setTime(0, 0, 0);
    
        $endMonth = $startmonth + 2;
        $this->endDate = new DateTime("$year-$endMonth-01");
        $this->endDate->modify('last day of this month');
        $this->endDate->setTime(23, 59, 59);
    
        return $this->get_result();
    }

    public function get_by_semester($semester, $year) {
        $semester = (int) $semester;
        if($semester < 1 || $semester > 2) {
            return null;
        }
        
        $startMonth = $semester * 6 - 5;
        $endMonth = $startMonth + 5;
        
        $this->startDate = new DateTime("$year-$startMonth-01");
        $this->startDate->modify('first day of this month');
        $this->startDate->setTime(0, 0, 0);
    
        $this->endDate = new DateTime("$year-$endMonth-01");
        $this->endDate->modify('last day of this month');
        $this->endDate->setTime(23, 59, 59);
    
        return $this->get_result();
    }
}