<?php

declare(strict_types=1);

$files = array_merge(
    glob('./config/dependencies/*.php') ?: [],
    glob('./config/*.php') ?: [],
);

return array_merge_recursive(
    ...array_map(
        fn(string $file) => require $file,
        $files
    )
);