<?php

if(!isset($tableData)) {
    throw new \Error('$tableData (multidimensional array) should be passed to view');
}

if(!isset($tableAttrs)) $tableAttrs = [];
if(!is_array($tableAttrs)) {
    throw new \Error('$tableAttrs (array) passed not as array');
}
if(isset($rowCount)) $tableAttrs['Jumlah Baris'] = $rowCount;

?><div class="container pt-5"><?php

if(isset($tableTitle) || count($tableAttrs) > 0) {

    ?><div class="row align-items-end mb-4"><?php

    if(isset($tableTitle)):
        ?><div class="col-auto">
            <h3 class="mb-0"><?=$tableTitle?></h3>
        </div><?php
    endif;

    foreach(array_keys($tableAttrs) as $attrIndex => $attrTitle):
        ?><div class="<?=$attrIndex === 0 ? 'col-auto ms-auto' : 'col-auto'?>">
            <p class="mb-0"><?=$attrTitle?>: <?=$tableAttrs[$attrTitle]?></p>
        </div><?php
    endforeach;

    ?></div><?php

}

?></div>
<div class="container-fluid mb-5"><?php

if(isset($debugText)) {


    ?><div>
        <pre><?=$debugText?></pre>
    </div><?php

}

if(count($tableData) < 1) {
    
    ?><p class="text-center mb-0 p-4 border">Data is empty.</p><?php

} else {

    $colNames = array_keys($tableData[0]);

    ?><div class="table-responsive mb-4">
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

?></div>