<?php

if(!isset($tableData)) {
    throw new \Error('$tableData (multidimensional array) should be passed to view');
}

require __DIR__ . '/header.php';

?><div class="container-fluid"><?php

if(count($tableData) < 1) {
    
    ?><h3>Data is empty.</h3><?php

} else {

    $colNames = array_keys($tableData[0]);

    ?><div class="table-responsive">
        <table class="table table-sm table-bordered">
        <tr>
            <th>no</th><?php

        foreach($colNames as $colName):
            ?><th><?=$colName?></th><?php
        endforeach;

        ?></tr><?php

    foreach($tableData as $index => $row):
        ?><tr>

            <td><?=($index + 1)?></td><?php

        foreach($colNames as $key):
            ?><td><?=$row[$key]?></td><?php
        endforeach;

        ?></tr><?php
    endforeach;

        ?></table>
    </div><?php

}

?></div><?php

require __DIR__ . '/footer.php';