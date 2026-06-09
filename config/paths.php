<?php

$documentRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');

if ($documentRoot === '') {
    $localPath = realpath(__DIR__ . '/../');
    $centralBase = realpath(__DIR__ . '/../../central');
    $CONFIG['LOCAL_PATH'] = ($localPath === false ? __DIR__ . '/../' : $localPath . '/');
    $CONFIG['CENTRAL_PATH'] = ($centralBase === false ? __DIR__ . '/../../central/' : $centralBase . '/');
    return;
}

$CONFIG['LOCAL_PATH'] = $documentRoot . '/';
$CONFIG['CENTRAL_PATH'] = dirname($documentRoot) . '/central/';
