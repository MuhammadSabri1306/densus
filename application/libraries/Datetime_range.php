<?php

class Datetime_range
{
    public $format;
    public $startDate;
    public $endDate;

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

    public function get_by_day($day, $month, $year)
    {
        $date = "$year-$month-$day";
        $this->startDate = new DateTime($date);
        $this->startDate->setTime(0, 0, 0);

        $this->endDate = new DateTime($date);
        $this->endDate->setTime(23, 59, 59);

        return $this->get_result();
    }

    public function get_by_year($year)
    {
        $this->startDate = new DateTime($year.'-01-01');
        $this->startDate->setTime(0, 0, 0);

        $this->endDate = new DateTime($year.'-12-31');
        $this->endDate->setTime(23, 59, 59);

        return $this->get_result();
    }

    public function get_by_year_range($startYear, $endYear)
    {
        $this->startDate = new DateTime($startYear.'-01-01');
        $this->startDate->setTime(0, 0, 0);

        $this->endDate = new DateTime($endYear.'-12-31');
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

    public function get_by_daterange_month($startDate, $endDate, $month, $year)
    {
        $this->startDate = new DateTime("$year-$month-$startDate");
        $this->startDate->setTime(0, 0, 0);

        $this->endDate = new DateTime("$year-$month-$endDate");
        $this->endDate->setTime(23, 59, 59);

        return $this->get_result();
    }

    public function get_by_month_range($startMonth, $endMonth, $year)
    {
        $this->startDate = new DateTime("$year-$startMonth-01");
        $this->startDate->setTime(0, 0, 0);

        $this->endDate = new DateTime("$year-$endMonth-01");
        $this->endDate->modify('last day of this month');
        $this->endDate->setTime(23, 59, 59);

        return $this->get_result();
    }

    public function get_by_quarter($quarter, $year) {
        $quarter = (int) $quarter;
        $startMonth = (($quarter - 1) * 3) + 1;

        $this->startDate = new DateTime("$year-$startMonth-01");
        $this->startDate->modify('first day of this month');
        $this->startDate->setTime(0, 0, 0);
    
        $endMonth = $startMonth + 2;
        $this->endDate = new DateTime("$year-$endMonth-01");
        $this->endDate->modify('last day of this month');
        $this->endDate->setTime(23, 59, 59);
    
        return $this->get_result();
    }

    public function get_quarter_by_month($month, $year) {
        $month = (int) $month;
        $quarter = ceil($month / 3);
        $startMonth = ($quarter - 1) * 3 + 1;

        $this->startDate = new DateTime("$year-$startMonth-01");
        $this->startDate->modify('first day of this month');
        $this->startDate->setTime(0, 0, 0);
    
        $endMonth = $startMonth + 2;
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