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

    public function request($url)
    {
        // parameters
        $params = array(
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => $this->accessToken,
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        );

        // generate and add signature parameter
        $queryParams = array();
        ksort($params);
        foreach ($params as $key => $value){
            $queryParams[] = $key.'='.rawurlencode($value);
        }
        $queryString = "GET&" . rawurlencode($url) . '&' . rawurlencode(implode('&', $queryParams));
        $compositeSecret = rawurlencode($this->consumerSecret) . '&' . rawurlencode($this->accessTokenSecret);
        $params['oauth_signature'] = base64_encode(hash_hmac('sha1', $queryString, $compositeSecret, true));

        $header = 'Authorization: OAuth ';
        $values = array();
        foreach ($params as $key => $value) {
            $values[] = $key.'="'.rawurlencode($value).'"';
        }
        $header .= implode(', ', $values);

        $options = array( CURLOPT_HTTPHEADER => array($header, 'Expect:'),
            //CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_HEADER => false,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false);

        $feed = curl_init();
        curl_setopt_array($feed, $options);
        $json = curl_exec($feed);
        curl_close($feed);

        return json_decode($json);
    }

    public function getTimeline()
    {
        $url = $this->apiUrl.'/statuses/user_timeline.json';
        $response = $this->request($url);

        return $response;
    }
}