<?php

namespace Byskr\Typo3GelfWriter\Writer;

class GelfWriterTest extends \PHPUnit_Framework_TestCase
{
    protected $obj;

    protected $initOptions = ['serverUrl' => 'localhost', 'serverPort' => '123'];

    public function setUp()
    {
        parent::setUp();

        $this->obj = new GelfWriter($this->initOptions);
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


    public function testInit()
    {
        $this->assertEquals($this->initOptions['serverUrl'], $this->getProperty('serverUrl'));
        $this->assertEquals($this->initOptions['serverPort'], $this->getProperty('serverPort'));
    }

    /**
     * @dataProvider setServerDataProvider
     * @param string $url
     */
    public function testSetServerUrl($url) {
        $this->invokeMethod('setServerUrl', [$url]);

        $this->assertEquals($url, $this->getProperty('serverUrl'));
    }

    public function setServerDataProvider() {
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
    public function testSetServerUrlException($url) {
        $this->invokeMethod('setServerUrl', [$url]);
    }

    public function setServerWrongDataProvider() {
        return [
            [''],
            [['foo']],
        ];
    }

    /**
     * @dataProvider setServerPortDataProvider
     * @param string $port
     */
    public function testSetServerPort($port) {
        $this->invokeMethod('setServerPort', [$port]);

        $this->assertEquals($port, $this->getProperty('serverPort'));
    }

    public function setServerPortDataProvider() {
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
    public function testSetServerPortException($port) {
        $this->invokeMethod('setServerPort', [$port]);
    }

    public function setServerPortWrongDataProvider() {
        return [
            ['adf'],
            [['foo']],
            ['12a3']
        ];
    }

    public function testWriteLog() {

    }

}
