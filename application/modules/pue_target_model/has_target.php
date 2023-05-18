<?php

// SELECT COUNT(id) AS count
// FROM pue_target
// WHERE created_at>='2023-04-01 00:00:00' AND created_at<='2023-06-30 23:59:59'
$filterDate = $this->get_datetime_filter($filter);
$this->db
    ->select('id')
    ->from($this->tableName)
    ->where($filterDate)
    ->order_by('created_at', 'DESC');
$count = $this->db->count_all_results();

$this->result = $count > 0 ? true : false;