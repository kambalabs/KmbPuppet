<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/kambalabs for the sources repositories
 *
 * This file is part of Kamba.
 *
 * Kamba is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Kamba is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kamba.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbPuppetTest;

use Zend\Http\Client;
use Zend\Http\Headers;
use Zend\Http\Request;

class FakeHttpClient extends Client
{
    /**
     * @var boolean
     */
    protected $failure = false;

    public function send(Request $request = null)
    {
        $response = $this->getMock('Zend\Http\Response');

        $headers = new Headers();
        $isSuccess = !$this->isFailure();
        $statusLine = 'HTTP/1.0 200 OK';
        $body = '';

        if ($this->isFailure()) {
            $statusLine = 'HTTP/1.0 500 Internal Server Error';
        }

        $response->expects($this->any())
            ->method('getHeaders')
            ->will($this->returnValue($headers));

        $response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($body));

        $response->expects($this->any())
            ->method('isSuccess')
            ->will($this->returnValue($isSuccess));

        $response->expects($this->any())
            ->method('renderStatusLine')
            ->will($this->returnValue($statusLine));

        return $response;
    }

    /**
     * Get Failure status.
     *
     * @return boolean
     */
    public function isFailure()
    {
        return $this->failure;
    }

    /**
     * Specify if the request should respond an internal server error (500)
     *
     * @param boolean $failure
     * @return FakeHttpClient
     */
    public function setFailure($failure)
    {
        $this->failure = $failure;
        return $this;
    }

    /**
     * Set failure to true
     *
     * @return FakeHttpClient
     */
    public function fails()
    {
        $this->setFailure(true);
        return $this;
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is executed zero or more times.
     *
     * @return \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     * @since  Method available since Release 3.0.0
     */
    public static function any()
    {
        return new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
    }

    /**
     * @param  mixed $value
     * @return \PHPUnit_Framework_MockObject_Stub_Return
     * @since  Method available since Release 3.0.0
     */
    public static function returnValue($value)
    {
        return new \PHPUnit_Framework_MockObject_Stub_Return($value);
    }

    /**
     * @param $originalClassName
     * @return object
     */
    protected function getMock($originalClassName)
    {
        return \PHPUnit_Framework_MockObject_Generator::getMock($originalClassName);
    }
}
