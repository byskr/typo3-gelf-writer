<?php

namespace Byskr\Typo3GelfWriter\Writer;

use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\HttpTransport;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophet;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class GelfWriterTest extends TestCase
{
    protected $obj;

    protected $initOptions = ['serverUrl' => 'localhost', 'serverPort' => '123'];

    public function setUp()
    {
        parent::setUp();

        $this->obj = new GelfWriter($this->initOptions);

        if (!array_key_exists('HTTP_HOST', $_SERVER)) {
            $_SERVER['HTTP_HOST'] = 'meinTestServer';
        }
        if (!array_key_exists('REQUEST_URI', $_SERVER)) {
            $_SERVER['REQUEST_URI'] = '/';
        }
        if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod($methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(GelfWriter::class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->obj, $parameters);
    }

    protected function getProperty($propertyName)
    {
        $class = new \ReflectionClass(GelfWriter::class);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($this->obj);
    }

    protected function setProperty($propertyName, $value)
    {
        $class = new \ReflectionClass(GelfWriter::class);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->obj, $value);
    }


    public function testInit()
    {
        $this->assertEquals($this->initOptions['serverUrl'], $this->getProperty('serverUrl'));
        $this->assertEquals($this->initOptions['serverPort'], $this->getProperty('serverPort'));
    }

    /**
     * @dataProvider setServerDataProvider
     * @param string $url
     */
    public function testSetServerUrl($url)
    {
        $this->invokeMethod('setServerUrl', [$url]);

        $this->assertEquals($url, $this->getProperty('serverUrl'));
    }

    public function setServerDataProvider()
    {
        return [
            ['123test.foo'],
            ['127.0.0.1'],
            ['foo.bar.de'],
            ['123test.foo/bang']
        ];
    }

    /**
     * @dataProvider setServerWrongDataProvider
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid Server URL
     * @param string $url
     */
    public function testSetServerUrlException($url)
    {
        $this->invokeMethod('setServerUrl', [$url]);
    }

    public function setServerWrongDataProvider()
    {
        return [
            [''],
            [['foo']],
        ];
    }

    /**
     * @dataProvider setServerPortDataProvider
     * @param string $port
     */
    public function testSetServerPort($port)
    {
        $this->invokeMethod('setServerPort', [$port]);

        $this->assertEquals($port, $this->getProperty('serverPort'));
    }

    public function setServerPortDataProvider()
    {
        return [
            [1],
            [1234]
        ];
    }

    /**
     * @dataProvider setServerPortWrongDataProvider
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid Server Port
     * @param string $port
     */
    public function testSetServerPortException($port)
    {
        $this->invokeMethod('setServerPort', [$port]);
    }

    public function setServerPortWrongDataProvider()
    {
        return [
            ['adf'],
            [['foo']],
            ['12a3']
        ];
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Configuration value is missing
     */
    public function testInvalidConstructor()
    {
        new GelfWriter([]);
    }

    public function testWriteLog()
    {
        $transport = $this->prophesize(HttpTransport::class);
        $publisher = $this->prophesize(Publisher::class);
        $logMessage = $this->prophesize(Message::class);

        $objectManager = $this->prophesize(ObjectManager::class);
        $this->setProperty('objectManager', $objectManager->reveal());

        $objectManager->get(HttpTransport::class, $this->initOptions['serverUrl'], $this->initOptions['serverPort'])
            ->willReturn($transport->reveal());

        $objectManager->get(Publisher::class, $transport)->willReturn($publisher->reveal());
        $objectManager->get(Message::class)->willReturn($logMessage->reveal());

        $logRecord = $this->prophesize(LogRecord::class);
        $logRecord->getLevel()->willReturn(0);
        $logRecord->getComponent()->willReturn('tx_myExt');
        $logRecord->getMessage()->willReturn('Fatal-Fatal');
        $logRecord->getData()->willReturn('myStacktrace');
        $logRecord->getRequestId()->willReturn('1');

        $logMessage->setVersion(GelfWriter::GELF_VERSION_STRING)->willReturn($logMessage->reveal());
        $logMessage->setHost('meinTestServer' . ' - ' . php_uname('n'))->willReturn($logMessage->reveal());
        $logMessage->setShortMessage('EMERGENCY - tx_myExt')->willReturn($logMessage->reveal());
        $logMessage->setFullMessage('Fatal-Fatal'. PHP_EOL .'myStacktrace')->willReturn($logMessage->reveal());
        $logMessage->setLevel('EMERGENCY')->willReturn($logMessage->reveal());
        $logMessage->setAdditional('RequestUrl', '/')->willReturn($logMessage->reveal());
        $logMessage->setAdditional('RequestMethod', 'GET')->willReturn($logMessage->reveal());
        $logMessage->setAdditional('RequestId', '1')->willReturn($logMessage->reveal());

        $this->obj->writeLog($logRecord->reveal());

    }

}
