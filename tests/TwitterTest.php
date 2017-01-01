<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Endroid\Twitter\Twitter;
use Endroid\Twitter\Tests\Util;
use Symfony\Component\HttpFoundation\Response;

class TwitterTest extends PHPUnit_Framework_TestCase
{
    const EXPECTED_OAUTH_HEADER_PARAMETERS = 'oauth_consumer_key=foo, oauth_nonce=1234567890, oauth_signature_method=HMAC-SHA1, oauth_timestamp=1234567890, oauth_token=baz, oauth_version=1.0';
    const EXPECTED_OAUTH_HEADER = 'OAuth %s, oauth_signature=';
    const EXPECTED_BEARER_HEADER = 'Bearer cc4f26cc4a3f61a84436014b2166e431';
    const EXPECTED_BASIC_HEADER = 'Basic Zm9vOmJhcg==';

    public function testGetQueryParameters()
    {
        $twitter = new Twitter('foo', 'bar');
        $parameters = ['a' => 'foo', 'b' => 'bar', 'c' => 'baz'];
        $header = Util::invokeMethod($twitter, 'getQueryParameters', [$parameters]);
        $this->assertEquals('a=foo&b=bar&c=baz', $header);
    }

    public function testGetBasicHeader()
    {
        $twitter = new Twitter('foo', 'bar');
        $header = Util::invokeMethod($twitter, 'getBasicHeader');
        $this->assertEquals(self::EXPECTED_BASIC_HEADER, $header);
    }

    public function testGetBasicHeaderInvalidParametersException()
    {
        $twitter = new Twitter(null, null);
        $this->setExpectedException('Endroid\Twitter\Exception\InvalidParametersException');
        Util::invokeMethod($twitter, 'getBasicHeader');
    }

    public function testGetBearerHeader()
    {
        $twitter = $this->getMockBuilder('Endroid\Twitter\Twitter')
            ->setConstructorArgs(['foo', 'bar'])
            ->setMethods(['getBasicHeader', 'call'])
            ->getMock();

        $twitter->expects($this->any())
            ->method('getBasicHeader')
            ->willReturn(self::EXPECTED_BASIC_HEADER);

        $response = new Response();
        $response->setContent(json_encode([
            'token_type' => 'bearer',
            'access_token' => 'cc4f26cc4a3f61a84436014b2166e431',
        ]));

        $twitter->expects($this->any())
            ->method('call')
            ->with('POST', Twitter::BASE_URL.Twitter::TOKEN_URL)
            ->willReturn($response);

        $header = Util::invokeMethod($twitter, 'getBearerHeader');
        $this->assertEquals(self::EXPECTED_BEARER_HEADER, $header);
    }

    public function testGetBearerHeaderInvalidResponseException()
    {
        $twitter = $this->getMockBuilder('Endroid\Twitter\Twitter')
            ->setConstructorArgs(['foo', 'bar'])
            ->setMethods(['getBasicHeader', 'call'])
            ->getMock();

        $twitter->expects($this->any())
            ->method('getBasicHeader')
            ->willReturn(self::EXPECTED_BASIC_HEADER);

        $twitter->expects($this->any())
            ->method('call')
            ->with('POST', Twitter::BASE_URL.Twitter::TOKEN_URL)
            ->willReturn(new Response());

        $this->setExpectedException('Endroid\Twitter\Exception\InvalidResponseException');
        Util::invokeMethod($twitter, 'getBearerHeader');
    }

    public function testGetBearerHeaderInvalidTokenTypeException()
    {
        $twitter = $this->getMockBuilder('Endroid\Twitter\Twitter')
            ->setConstructorArgs(['foo', 'bar'])
            ->setMethods(['getBasicHeader', 'call'])
            ->getMock();

        $twitter->expects($this->any())
            ->method('getBasicHeader')
            ->willReturn(self::EXPECTED_BASIC_HEADER);

        $response = new Response();
        $response->setContent(json_encode([
            'token_type' => 'something_wrong',
            'access_token' => 'cc4f26cc4a3f61a84436014b2166e431',
        ]));

        $twitter->expects($this->any())
            ->method('call')
            ->with('POST', Twitter::BASE_URL.Twitter::TOKEN_URL)
            ->willReturn($response);

        $this->setExpectedException('Endroid\Twitter\Exception\InvalidTokenTypeException');
        Util::invokeMethod($twitter, 'getBearerHeader');
    }

    public function testGetOAuthHeader()
    {
        $twitter = $this->getMockBuilder('Endroid\Twitter\Twitter')
            ->setConstructorArgs(['foo', 'bar', 'baz', 'test'])
            ->setMethods(['getQueryParameters'])
            ->getMock();

        $twitter->expects($this->any())
            ->method('getQueryParameters')
            ->willReturn(self::EXPECTED_OAUTH_HEADER_PARAMETERS);

        $header = Util::invokeMethod($twitter, 'getOAuthHeader', ['https://domain.tld/']);
        $this->assertContains(sprintf(self::EXPECTED_OAUTH_HEADER, self::EXPECTED_OAUTH_HEADER_PARAMETERS), $header);
    }

    public function testGetOAuthHeaderInvalidParametersException()
    {
        $twitter = new Twitter('foo', 'bar');
        $this->setExpectedException('Endroid\Twitter\Exception\InvalidParametersException');
        Util::invokeMethod($twitter, 'getOAuthHeader', ['https://domain.tld/']);
    }

    public function testGetAuthorizationBearer()
    {
        $twitter = $this->getMockBuilder('Endroid\Twitter\Twitter')
            ->setConstructorArgs(['foo', 'bar'])
            ->setMethods(['getOAuthHeader', 'getBearerHeader'])
            ->getMock();

        $twitter->expects($this->any())
            ->method('getBearerHeader')
            ->willReturn(self::EXPECTED_BEARER_HEADER);

        $twitter->expects($this->any())
            ->method('getOAuthHeader')
            ->willReturn(sprintf(self::EXPECTED_OAUTH_HEADER, self::EXPECTED_OAUTH_HEADER_PARAMETERS));

        $authorization = Util::invokeMethod($twitter, 'getAuthorization', ['https://domain.tld/']);
        $this->assertEquals(self::EXPECTED_BEARER_HEADER, $authorization);
    }

    public function testGetAuthorizationOAuth()
    {
        $twitter = $this->getMockBuilder('Endroid\Twitter\Twitter')
            ->setConstructorArgs(['foo', 'bar', 'baz', 'test'])
            ->setMethods(['getOAuthHeader', 'getBearerHeader'])
            ->getMock();

        $twitter->expects($this->any())
            ->method('getBearerHeader')
            ->willReturn(self::EXPECTED_BEARER_HEADER);

        $twitter->expects($this->any())
            ->method('getOAuthHeader')
            ->willReturn(sprintf(self::EXPECTED_OAUTH_HEADER, self::EXPECTED_OAUTH_HEADER_PARAMETERS));

        $authorization = Util::invokeMethod($twitter, 'getAuthorization', ['https://domain.tld/']);
        $this->assertEquals(sprintf(self::EXPECTED_OAUTH_HEADER, self::EXPECTED_OAUTH_HEADER_PARAMETERS), $authorization);
    }
}
