<?php
namespace icezyburg\WebCeo;

/**
 * 
 * @author Pattamakorn Samhadthai <icezyburg@gmail.com>
 * @license MIT
 * @version 1.0
 *
**/

class WebCeo
{

    private $api_url = "https://online.webceo.com/api/";

    private $_curl_handle = NULL;
    private $_debug_info = NULL;

    const HTTP_METHOD_POST = 'POST';

    public function get(array $query = array())
    {
 
        if (!isset($query['key']) || $query['key'] === NULL)
            throw new \InvalidArgumentException('Invalid api key - make sure api_key is defined in the config array');

        return $this->_makeRequest($query);
    }

    public function getDebugInfo()
    {
        return $this->_debug_info;
    }

    private function _makeRequest($query, $method = "POST")
    {
        $ch = $this->_getCurlHandle();

        $options = array(
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_POSTFIELDS => "json=".urlencode(json_encode($query)),
            CURLOPT_RETURNTRANSFER => true
        );

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        $this->_debug_info = curl_getinfo($ch);

        $response = json_decode($response);

        if (isset($response['result']) && $response['result'] > 0) {
            throw new \RuntimeException('Request Error: ' . $response['errormsg'] . '. Raw Response: ' . print_r($response, true));
        }

        return $response;

    }

    protected function _getCurlHandle()
    {
        if (!$this->_curl_handle) {
            $this->_curl_handle = curl_init($this->api_url);
        }
        return $this->_curl_handle;
    }
    
    public function __destruct()
    {
        if ($this->_curl_handle) {
            curl_close($this->_curl_handle);
        }
    }
}