<?php

header('Content-Type: text/plain; charset=utf-8');

echo "DATABASE_URL = ";
var_dump(getenv('DATABASE_URL'));

echo "\n\nAll DB_* vars:\n";
foreach (getenv() as $k => $v) {
    if (str_starts_with($k, 'DB_')) {
        echo $k, ' = ', $v, PHP_EOL;
    }
}
