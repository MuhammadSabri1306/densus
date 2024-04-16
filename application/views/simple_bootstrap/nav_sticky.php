<?php

if(!isset($content) || !is_callable($content)) {
    throw new \Error('$content (callable) should be passed to view');
}

?><nav class="navbar sticky-top bg-body-tertiary">
    <div class="container-fluid"><?php

        $content();

    ?></div>
</nav>