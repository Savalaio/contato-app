<?php
$input = file_get_contents("php://input");

file_put_contents(
    __DIR__ . "/mensagens.json",
    $input . PHP_EOL,
    FILE_APPEND
);