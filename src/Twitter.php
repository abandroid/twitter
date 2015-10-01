<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Twitter;

use Buzz\Browser;
use Buzz\Client\Curl;

class Twitter
{
    /**
     * @var string
     */
    protected $apiUrl = 'https://api.twitter.com/1.1/';

    /**
     * @var string
     */
    protected $consumerKey;

    /**
     * @var string
     */
    protected $consumerSecret;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $accessTokenSecret;

    /**
     * @var Browser
     */
    protected $browser;

    /**
     * Class constructor.
     *
     * @param $consumerKey
     * @param $consumerSecret
     * @param $accessToken
     * @param $accessTokenSecret
     * @param string|null $apiUrl
     * @param string|null $proxy
     * @param $timeout
     */
    public function __construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret, $apiUrl = null, $proxy = null, $timeout = 5)
    {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->accessToken = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;

        if ($apiUrl) {
            $this->apiUrl = (string) $apiUrl;
        }

        $curl = new Curl();

        if ($proxy) {
            $curl->setProxy($proxy);
        }

        $curl->setTimeout($timeout);
        $this->browser = new Browser($curl);
    }

    /**
     * Performs a query to the Twitter API.
     *
     * @param $name
     * @param string $method
     * @param string $format
     * @param array  $parameters
     *
     * @return \Buzz\Message\Response
     */
    public function query($name, $method = 'GET', $format = 'json', $parameters = array())
    {
        $oauthParameters = array(
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_token' => $this->accessToken,
            'oauth_version' => '1.0',
        );

        // Part 1 : http method
        $httpMethod = $method;

        // Part 2 : base url
        $baseUrl = $this->apiUrl.$name.'.'.$format;

        // Part 3 : parameter string
        $oauthParameters = array_merge($parameters, $oauthParameters);
        ksort($oauthParameters);
        $parameterQueryParts = array();
        foreach ($oauthParameters as $key => $value) {
            $parameterQueryParts[] = $key.'='.rawurlencode($value);
        }
        $parameterString = implode('&', $parameterQueryParts);

        // Build signature string from part 1, 2 and 3
        $signatureString = strtoupper($httpMethod).'&'.rawurlencode($baseUrl).'&'.rawurlencode($parameterString);
        $signatureKey = rawurlencode($this->consumerSecret).'&'.rawurlencode($this->accessTokenSecret);
        $signature = base64_encode(hash_hmac('sha1', $signatureString, $signatureKey, true));

        // Create headers containing oauth
        $parameterQueryParts[] = 'oauth_signature='.rawurlencode($signature);
        $oauthHeader = 'OAuth '.implode(', ', $parameterQueryParts);
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: '.$oauthHeader,
        );

        // The call has to be made against the base url + query string
        if (count($parameters) > 0) {
            $requestQueryParts = array();
            foreach ($parameters as $key => $value) {
                $requestQueryParts[] = $key.'='.rawurlencode($value);
            }
            $baseUrl .= '?'.implode('&', $requestQueryParts);
        }

        // Perform cURL request
        if (strtoupper($method) == 'GET') {
            $response = $this->browser->get($baseUrl, $headers);
        } else {
            $response = $this->browser->post($baseUrl, $headers);
        }

        return $response;
    }

    /**
     * Returns the user timeline.
     *
     * @param $parameters
     *
     * @return mixed
     */
    public function getTimeline($parameters)
    {
        $response = $this->query('statuses/user_timeline', 'GET', 'json', $parameters);

        return json_decode($response->getContent());
    }
}
