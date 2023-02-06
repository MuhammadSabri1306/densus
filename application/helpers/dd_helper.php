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

        #ddContainer *:not(font) {
            color: #000;
        }

        #ddContainer pre {
            background-color: transparent;
        }

        .loader-wrapper,
        .page-main-header {
            display: none!important;
        }
    </style>
    <div id="ddContainer">
        <p>dd() from <?="{$calledFrom}"?></p><?php

    foreach($items as $item) {
        var_dump($item);
    }

    ?></div><?php

    exit();
}