# Typo3 Log Writer for Greylog

This Logger uses the TCP Input of greylog

see https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Logging/Configuration/Index.html
and http://docs.graylog.org/en/2.3/pages/gelf.html

### Configuration 

    $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'] = [
        \TYPO3\CMS\Core\Log\LogLevel::ERROR => [
            'Byskr\Typo3GelfWriter\Writer\GelfWriter' => [
                'serverUrl' => 'your-greylog-tcp-address'
                'serverPort' => 12345
            ]
        ]
    ];

