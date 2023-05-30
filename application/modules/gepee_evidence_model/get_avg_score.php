<?php

$locationList = $this->get_location_list($filter);
$scores = array_column($locationList, 'scores');
$this->result = count($scores) < 1 ? 0 : array_sum($scores) / count($scores);
return $this->result;