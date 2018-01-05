<?php

$GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'] = [
    \Psr\Log\LogLevel::DEBUG => [
        'Byskr\\Writer\\GelfWriter' => []
    ]
];
