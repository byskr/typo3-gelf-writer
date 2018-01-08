<?php

namespace Byskr\Typo3GelfWriter\Writer;

use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\HttpTransport;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;
use TYPO3\CMS\Core\Log\Writer\WriterInterface;
use TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class GelfWriter
 * @package Byskr\Typo3GelfWriter\Writer
 */
class GelfWriter extends AbstractWriter implements WriterInterface
{
    /**
     *
     */
    public const GELF_VERSION_STRING = '1.1';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $serverUrl;

    /**
     * @var string
     */
    protected $port;

    /**
     * GelfWriter constructor.
     * @param $options
     */
    public function __construct($options)
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        parent::__construct($options);
    }

    /**
     * @param LogRecord $record
     *
     * @return void
     */
    public function writeLog(LogRecord $record)
    {
        /** @var $transport HttpTransport */
        $transport = $this->objectManager->get(HttpTransport::class, $this->serverUrl, $this->port);
        /** @var Publisher $publisher */
        $publisher = $this->objectManager->get(Publisher::class, $transport);

        $message = $this->getMessage($record);

        $publisher->publish($message);
    }

    /**
     * @param LogRecord $record
     *
     * @return Message
     */
    protected function getMessage(LogRecord $record)
    {
        $host = $_SERVER['HTTP_HOST'] . ' - ' . php_uname('n');
        $logLevel = LogLevel::getName($record->getLevel());
        $shortMessageText = $logLevel . ' - ' . $record->getComponent();
        $messageText = $record->getMessage() . PHP_EOL . print_r($record->getData(), 1);

        /** @var Message $logMessage */
        $logMessage = $this->objectManager->get(Message::class);
        $logMessage
            ->setVersion(self::GELF_VERSION_STRING)
            ->setHost($host)
            ->setShortMessage($shortMessageText)
            ->setFullMessage($messageText)
            ->setLevel($logLevel)
            ->setAdditional('RequestUrl', $_SERVER['REQUEST_URI'])
            ->setAdditional('RequestMethod', $_SERVER['REQUEST_METHOD'])
            ->setAdditional('RequestId', $record->getRequestId());

        return $logMessage;
    }

    /**
     * @param string $serverUrl
     * @throws InvalidConfigurationException
     */
    protected function setServer($serverUrl)
    {
        if (!filter_var($serverUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidConfigurationException('Invalid Server URL');
        }

        $this->serverUrl = $serverUrl;
    }

    /**
     * @param string $port
     * @throws InvalidConfigurationException
     */
    protected function setPort($port)
    {
        if (!filter_var(port, FILTER_VALIDATE_INT)) {
            throw new InvalidConfigurationException('Invalid Server Port');
        }

        $this->port = $port;
    }
}
