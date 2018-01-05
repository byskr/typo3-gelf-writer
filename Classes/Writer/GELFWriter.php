<?php

namespace Byskr\Typo3GelfWriter\Writer;

use TYPO3\CMS\Core\Log\Writer\AbstractWriter;
use TYPO3\CMS\Core\Log\Writer\WriterInterface;

class GELFWriter extends AbstractWriter implements WriterInterface
{
    public function writeLog(\TYPO3\CMS\Core\Log\LogRecord $record)
    {
        var_dump($record);
    }
}
