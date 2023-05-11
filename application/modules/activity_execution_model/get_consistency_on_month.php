<?php

$currMonth = (int) date('n');
$queryMonthArr = [];
for($month=1; $month<=$currMonth; $month++) {
        array_push($queryMonthArr, "SELECT $month AS month");
}
$queryMonthList = implode(' UNION ', $queryMonthArr);

$filterTotal = [ 's.value=1', 'MONTH(e.created_at)=m.month', "YEAR(e.created_at)=$currYear" ];
if(isset($currUser)) {
    extract($currUser, EXTR_PREFIX_ALL, 'user');
    if(isset($user_level) && $user_level=='witel') {
        array_push($filterTotal, "l.witel_kode='$user_locationId'");
    } elseif(isset($user_level) && $user_level=='divre') {
        array_push($filterTotal, "l.divre_kode='$user_locationId'");
    }
}

$filterApproved = array_merge([ 'status="approved"' ], $filterTotal);
$filterApproved = implode(' AND ', $filterApproved);
$filterTotal = implode(' AND ', $filterTotal);
$querySelect = "SELECT COUNT(*) FROM $this->tableName AS e
    JOIN $this->tableScheduleName AS s ON s.id=e.id_schedule
    JOIN $this->tableLocationName AS l ON l.id=s.id_lokasi";

$queryApproved = "$querySelect WHERE $filterApproved";
$queryTotal = "$querySelect WHERE $filterTotal";

$sql_query = "SELECT m.month, (($queryApproved)/($queryTotal)*100) AS percent FROM ($queryMonthList) AS m";
$query = $this->db->query($sql_query);

$this->result = $query->result();