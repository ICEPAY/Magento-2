<?php namespace Icepay\API;

/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016, ICEPAY B.V. All rights reserved.
 */

use Icepay\API\Resources\Payment;
use Icepay\API\Resources\Refund;

class Client
{

    private static $instance;
    
    /**
     * @var $ch
     */
    protected $ch;

    /**
     * @var $api_key
     */
    public $api_key;

    /**
     * @var $api_secret
     */
    public $api_secret;

    /**
     * @var $api_completed_url
     */
    public $api_completed_url;

    /**
     * @var $api_error_url
     */
    public $api_error_url;

    /**
     * @var $api_endpoint string
     */
    public $api_endpoint = 'https://connect.icepay.com/webservice/api/v1/';

    /**
     * @var $api_version string
     */
    private $api_version = '0.0.2';

    public function getReleaseVersion()
    {
        return $this->api_version;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Supported curl methods
     */
    public $api_get = "GET";
    public $api_post = "POST";

    /**
     * Set the API Key and trim whitespaces
     *
     * @param $api_key
     */
    public function setApiKey($api_key)
    {
        $this->api_key = trim($api_key);
    }

    /**
     * Set the API Secret code and trim whitespaces
     *
     * @param $api_secret
     */
    public function setApiSecret($api_secret)
    {
        $this->api_secret = trim($api_secret);
    }

    /**
     * Set the completed url after a succesfull payment and trim whitespaces.
     *
     * @param $url
     */
    public function setCompletedURL($url)
    {
        $this->api_completed_url = trim($url);
    }

    /**
     * Set the error url after a error in the payment and trim whitespaces.
     *
     * @param $url
     */
    public function setErrorURL($url)
    {
        $this->api_error_url = trim($url);
    }

    /**
     * Generates a checksum to sign the message
     *
     * @param $string
     * @return string
     */
    public function generateChecksum($string)
    {
        return hash('sha256', $string);
    }

    /**
     * API Constructor
     */
    public function __construct()
    {
        $this->payment = new Payment($this);
        $this->refund = new Refund($this);
    }

    /**
     * Request function to call our API Rest Payment Server
     *
     * @param $method
     * @param $api_method
     * @param $body
     * @param $checksum
     *
     * @return mixed
     * @throws \Exception
     */
    public function request($method, $api_method, $body = null, $checksum)
    {
        /**
         * Check if the Merchant ID is set
         */
        if (empty($this->api_key)) {
            throw new \Exception("Please configure your ICEPAY Merchant ID.");
        }

        /**
         * Check if the Secret Code is set
         */
        if (empty($this->api_secret)) {
            throw new \Exception("Please configure your ICEPAY Secret Code.");
        }

        /**
         * Check if the CompletedURL is set
         */
        if (empty($this->api_completed_url)) {
            throw new \Exception("Please configure your setCompletedURL()");
        }

        /**
         * Check if the ErrorURL is set
         */
        if (empty($this->api_error_url)) {
            throw new \Exception("Please configure your setErrorURL()");
        }

        /**
         * Start a curl session
         */
        if (empty($this->ch) || !function_exists("curl_reset")) {
            $this->ch = curl_init();
        } else {
            curl_reset($this->ch);
        }

        /**
         * Set the curl options
         */
        curl_setopt($this->ch, CURLOPT_URL, $this->api_endpoint . $api_method);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->ch, CURLOPT_HEADER, true);

        /**
         * Possible output: 5.6.9
         */
        $php_version = phpversion();

        /**
         * Prepare the curl headers
         */
        $api_headers = [
            "MerchantID: {$this->api_key}",
            "Checksum: {$checksum}",
            "User-Agent: ICEPAY API/{$this->api_version} PHP/{$php_version}",
            "Accept: application/json"
        ];

        /**
         * If the body is not null, let curl post the request as json content
         */
        if ($body !== null) {
            $api_headers[] = "Content-Type: application/json";

            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        /**
         * Set the curl headers for the payment server
         */
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $api_headers);

        /**
         * Set the SSL options
         */
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);

        /**
         * Execute the request
         */
        $response = curl_exec($this->ch);

        /**
         * Invalid or no certificate authority found, using bundled information
         */
        if (curl_errno($this->ch) == 77 /* CURLE_SSL_CACERT_BADFILE */ || curl_errno($this->ch) == 60 /* CURLE_SSL_CACERT */) {
            curl_setopt($this->ch, CURLOPT_CAINFO, realpath(dirname(__FILE__) . '/Assets/icepay.pem'));

            $response = curl_exec($this->ch);
        }

//        /**
//         * Verifying peer certificate fails on OpenSSL 0.9 or earlier.
//         * We done all we could to check the certificate on the host.
//         * This webserver simply will not accept it and we need to connect.
//         */
//        Uncommenting this code block can be insecure, use at your own risk
        if (strpos(curl_error($this->ch), 'certificate subject name') !== false) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);

            $response = curl_exec($this->ch);
        }

        /**
         * Check if we got any error, if so, exception it
         */
        if (curl_errno($this->ch)) {
            $exception_no = curl_errno($this->ch);
            $exception = curl_error($this->ch);

            curl_close($this->ch);
            $this->ch = null;

            throw new \Exception('Unable to reach the ICEPAY payment server (' . $exception_no . '):' . $exception);
        }

        /**
         * Separate headers and response body
         */
        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $response_header = substr($response, 0, $header_size);
        $response_body = substr($response, $header_size);

        /**
         * Close the connection
         */
        if (!function_exists("curl_reset")) {
            curl_close($this->ch);
            $this->ch = null;
        } else {
            curl_reset($this->ch);
            $this->ch = null;
        }

        /**
         * Verify response checksum. If it does not match, throw an exception.
         * Always require presence of a checksum header.
         */
        $parsed_headers = $this->parse_headers($response_header);
        if (isset($parsed_headers[0]["Checksum"])) {
            $checksumVerification = $this->generateChecksum(
                $this->api_endpoint .
                $api_method .
                $this->api_post .
                $this->api_key .
                $this->api_secret .
                $response_body
            );
            if ($checksumVerification != $parsed_headers[0]["Checksum"]) {
                throw new \Exception("Response checksum invalid");
            }
        } else {
            //if no checksum header was present in the response, the most likely cause is that the sender ID was invalid
            throw new \Exception("Response checksum not found. Verify your merchant ID.");
        }

        /**
         * Return the decoded json response
         */
        return json_decode($response_body);
    }

    /**
     * Close resources if they are open
     */
    public function __destruct()
    {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
        }
    }

    private function parse_headers($headertext)
    {
        $headers = [];

        /**
         * Split headers by newline
         */
        $rawHeaders = explode("\r\n\r\n", $headertext);
        /**
         * Loop through headers, split on semicolon and push to array
         * Stop at last item: it's just the empty line to separate the headers from the body
         */
        for ($index = 0; $index < count($rawHeaders) - 1; $index++) {
            foreach (explode("\r\n", $rawHeaders[$index]) as $i => $line) {
                if ($i === 0) {
                    $headers[$index]['http_code'] = $line;
                } else {
                    list ($key, $value) = explode(': ', $line);
                    $headers[$index][$key] = $value;
                }
            }
        }

        return $headers;
    }
}
