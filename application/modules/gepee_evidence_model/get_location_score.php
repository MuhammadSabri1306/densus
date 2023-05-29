<?php

$locFilter = $this->get_location_filter($filter, 'loc');
$dateFilter = $this->get_datetime_filter($filter, 'evd');
$filter = array_merge($locFilter, $dateFilter, [ 'cat.use_target' => 1 ]);

$this->db
    ->select('evd.*')
    ->from("$this->tableName AS evd")
    ->join("$this->tableCategoryName AS cat", 'cat.id_category=evd.id_category')
    ->join("$this->tableLocationName AS loc", 'loc.id_location=evd.id_location')
    ->where($filter)
    ->group_by('evd.id_category');
$checkedCount = $this->db->count_all_results();

$this->db
    ->select()
    ->from($this->tableCategoryName)
    ->where('use_target', 1);
$targetCount = $this->db->count_all_results();
// dd($checkedCount, $targetCount);
$this->result = $checkedCount / $targetCount * 100;