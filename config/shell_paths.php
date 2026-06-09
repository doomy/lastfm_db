<?php

$localPath = realpath(__DIR__ . '/../');
$centralPath = realpath(__DIR__ . '/../../central');

$CONFIG['LOCAL_PATH'] = $localPath === false ? __DIR__ . '/../' : $localPath . '/';
$CONFIG['CENTRAL_PATH'] = $centralPath === false ? __DIR__ . '/../../central/' : $centralPath . '/';
