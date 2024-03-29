<?php

function dd(...$items) {
    if(count($items) < 1) return;
    
    $t = debug_backtrace();
    $calledFrom = $t[0]['file'];
    
    ?><style>
        #ddContainer {
            width: 100vw;
            height: 100vh;
            background-color: #f8fafa;
            position: fixed;
            top: 0;
            left: 0;
            padding: 2rem;
            z-index: 9999;
        }

        #ddContainer *:not(font) { color: #000; }

        #ddContainer pre {
            background-color: #f6f8fa;
            padding: 10px;
        }

        #ddContainer strong { color: #e91e63; }

        .loader-wrapper,
        .page-main-header {
            display: none!important;
        }
    </style>
    <div id="ddContainer">
        <p>dd() from <?="{$calledFrom}"?></p><?php

    foreach($items as $item) {
        echo '<pre>';
        var_dump($item);
        echo '</pre>';
    }

    ?></div><?php

    exit();
}

function dd_json(...$items) {
    if(count($items) < 1) return;

    header('Content-type: application/json');
    echo json_encode($items);
    exit();
}