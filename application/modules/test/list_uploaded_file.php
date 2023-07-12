<?php

$this->load->database('densus');
$tableList = [
    [ 'title' => 'Activity Execution', 'tableName' => 'activity_execution', 'tableField' => 'evidence', 'uploadPath' => UPLOAD_ACTIVITY_EVIDENCE_PATH ],
    [ 'title' => 'Gepee Evidence', 'tableName' => 'gepee_evidence', 'tableField' => 'file', 'uploadPath' => UPLOAD_GEPEE_EVIDENCE_PATH ],
    [ 'title' => 'Pue Offline', 'tableName' => 'pue_offline', 'tableField' => 'evidence', 'uploadPath' => UPLOAD_PUE_EVIDENCE_PATH ],
    [ 'title' => 'OXISP', 'tableName' => 'oxisp_activity', 'tableField' => 'evidence', 'uploadPath' => UPLOAD_OXISP_EVIDENCE_PATH ],
];

$data = [];
foreach($tableList as $tableItem) {
    $this->db
        ->select($tableItem['tableField'].' AS file, updated_at')
        ->from($tableItem['tableName']);
    $result = $this->db->get()->result_array();
    
    foreach($result as $item) {
        $row = [
            'title' => $tableItem['title'],
            'dir' => $tableItem['uploadPath'],
            'path' => $tableItem['uploadPath'].$item['file'],
            'fullpath' => FCPATH.$tableItem['uploadPath'].$item['file'],
            'filename' => $item['file'],
            'updated_at' => $item['updated_at']
        ];
        $row['isExists'] = file_exists($row['fullpath']);
        array_push($data, $row);
    }

}

$no = 1;
?><style>
    .table-responsive { width: 100%; height: 90vh; overflow: auto; border: 1px solid black; }
    .table-responsive table { min-width:100% }
    .table-responsive tr:first-child th { position: sticky; top: 0; }
    .table-responsive tr *:nth-child(2) { position: sticky; left: 0; z-index: 2; }
    .table-responsive tr:first-child *:nth-child(2) { z-index: 3; }
    table { border: none; border-collapse: separate; border-spacing: 0; }
    td, th { border: 1px solid #000; background: #fff; }
</style>
<div class="table-responsive"><table>
    <tr>
        <th>No</th>
        <th>Tipe</th>
        <th>Direktori</th>
        <th>File Path</th>
        <th>Full Path</th>
        <th>Filename (raw DB)</th>
        <th>Tgl. Update</th>
        <th>Exists</th>
    </tr><?php

    foreach($data as $row) {

        ?><tr>
            <td><?=$no?></td>
            <td><?=$row['title']?></td>
            <td><?=$row['dir']?></td>
            <td><?=$row['path']?></td>
            <td><?=$row['fullpath']?></td>
            <td><?=$row['filename']?></td>
            <td><?=$row['updated_at']?></td>
            <td><?=$row['isExists'] ? 'Ada' : 'Tidak Ada'?></td>
        </tr><?php

        $no++;
    }

?></table></div>