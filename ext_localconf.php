<?php

$GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'] = [
    \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
        'Byskr\\Typo3GelfWriter\\Writer\\GELFWriter' => []
    ]
];
