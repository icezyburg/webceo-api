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

    public function get($key = NULL, $endpoint, array $condition = array(), array $curl_options = array())
    {
        if (!isset($key) || $key === NULL)
            throw new \InvalidArgumentException('Invalid api key - make sure api_key is defined in the config array');

        return $this->_makeRequest($key, $endpoint, $condition);
    }

    public function getDebugInfo()
    {
        return $this->_debug_info;
    }

    private function _makeRequest($key, $endpoint, $condition, $method = "POST")
    {
        $command = array("key" => $key, "method" => $endpoint, "data" => $condition);

        $ch = $this->_getCurlHandle();

        $options = array(
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_URL => $this->api_url,
            CURLOPT_POSTFIELDS => "json=".urlencode(json_encode($command)),
            CURLOPT_RETURNTRANSFER => true
        );

        if (!empty($curl_options)) {
            $options = array_replace($options, $curl_options);
        }
        if (isset($this->_config['curl_options']) && !empty($this->_config['curl_options'])) {
            $options = array_replace($options, $this->_config['curl_options']);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        $this->_debug_info = curl_getinfo($ch);

        $response = json_decode($response, true);

        if (isset($response['result']) && $response['result'] > 0) {
            throw new \RuntimeException('Request Error: ' . $response['errormsg'] . '. Raw Response: ' . print_r($response, true));
        }

        return $response;

    }

    protected function _getCurlHandle()
    {
        if (!$this->_curl_handle) {
            $this->_curl_handle = curl_init();
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