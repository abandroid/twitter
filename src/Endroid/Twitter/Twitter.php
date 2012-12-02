<?php

namespace Endroid\Twitter;

use Buzz\Browser;
use Buzz\Client\Curl;

class Twitter
{
    /**
     * @var string
     */
    protected $apiUrl = 'https://api.twitter.com/1.1';

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
     * Class constructor
     *
     * @param $consumerKey
     * @param $consumerSecret
     * @param $accessToken
     * @param $accessTokenSecret
     * @param null $apiUrl
     */
    public function __construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret, $apiUrl = null)
    {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->accessToken = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;

        if ($apiUrl) {
            $this->apiUrl = $apiUrl;
        }

        $this->browser = new Browser(new Curl());
    }

    /**
     * Performs a query to the Twitter API.
     *
     * @param $name
     * @param $method
     * @param array $parameters
     * @return mixed
     */
    public function query($name, $method, $parameters = array())
    {
        $oauthParameters = array(
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_token' => $this->accessToken,
            'oauth_version' => '1.0'
        );

        // Part 1 : http method
        $httpMethod = $method;

        // Part 2 : base url
        $baseUrl = $this->apiUrl.$name.'.json';

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

        // Create oauth header
        $parameterQueryParts[] = 'oauth_signature='.rawurlencode($signature);
        $oauthHeader = 'OAuth '.implode(', ', $parameterQueryParts);

        // The call has to be made against the base url + query string
        if (count($parameters) > 0) {
            $requestQueryParts = array();
            foreach ($parameters as $key => $value) {
                $requestQueryParts[] = $key.'='.rawurlencode($value);
            }
            $baseUrl .= '?'.implode('&', $requestQueryParts);
        }

        // Perform curl request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $baseUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: '.$oauthHeader
        ));
        if (strtolower($httpMethod) == 'post') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        }
        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        return $response;
    }

    /**
     * Retrieves the current user's timeline.
     *
     * @param array $parameters
     * @return mixed
     */
    public function getTimeline($parameters = array())
    {
        $defaults = array(
            'count' => 200
        );

        $parameters = $parameters + $defaults;

        $response = $this->query('/statuses/user_timeline', 'GET', $parameters);

        return $response;
    }
}