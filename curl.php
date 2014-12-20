<?php

/**
 * @author Agus Suroyo <jony.extenz@gmail.com>
 * @link http://agussuroyo.com/ Agus Suroyo
 * @version 1.0
 * @since 1.0
 * @copyright (c) 2014, Agus Suroyo
 */
class Curl
{

    var $ch;
    var $error;
    var $info;
    var $response;
    var $_resp;
    protected $use_auth;
    protected $username;
    protected $password;
    protected $method;
    protected $server;

    /**
     * Initialize all dependencies
     */
    public function __construct()
    {
        is_callable('curl_init') OR exit('cURL is not installed in your system.');
        $this->response = FALSE;
        $this->_resp = array();
        $this->use_auth = FALSE;
        $this->username = '';
        $this->password = '';
        $this->server = (is_callable('filter_input_array')) ? filter_input_array(INPUT_SERVER) : $_SERVER;
        $this->init();
    }

    /**
     * 
     * @param string $username
     * @param string $password
     * @return \Curl
     */
    public function setAuth($username = '', $password = '')
    {
        $this->use_auth = TRUE;
        if (!empty($username))
        {
            $this->username = $username;
        }
        if (!empty($password))
        {
            $this->password = $password;
        }
        return $this;
    }

    /**
     * 
     * @return \Curl
     */
    public function init()
    {
        $this->ch = curl_init();
        return $this;
    }

    /**
     * 
     * @param constants $option
     * @param boolean $value
     * @return \Curl
     */
    public function opt($option, $value = TRUE)
    {
        if (empty($this->ch))
        {
            $this->init();
        }
        curl_setopt($this->ch, $option, $value);
        return $this;
    }

    /**
     * 
     * @param resource $ch
     * @return \Curl
     */
    public function exec($ch = '')
    {
        if (!empty($ch))
        {
            $this->ch = $ch;
        }
        $this->response = curl_exec($this->ch);
        if (!$this->response)
        {
            $this->error();
            $this->response = $this->error;
        }
        $this->_resp[] = $this->response;
        return $this;
    }

    /**
     * 
     * @param resource $ch
     * @return \Curl
     */
    public function close($ch = '')
    {
        if (!empty($ch))
        {
            $this->ch = $ch;
        }
        curl_close($this->ch);
        return $this;
    }

    /**
     * 
     * @param resource $ch
     * @param constants $options
     * @return \Curl
     */
    public function info($ch = '', $options = '')
    {
        if (!empty($ch))
        {
            $this->ch = $ch;
        }
        $this->info = curl_getinfo($this->ch, $options);
        return $this;
    }

    /**
     * 
     * @param resource $ch
     * @return \Curl
     */
    public function error($ch = '')
    {
        if (!empty($ch))
        {
            $this->ch = $ch;
        }
        $this->error = curl_error($this->ch);
        return $this;
    }

    /**
     * 
     * @param resource $ch
     * @return \Curl
     */
    public function reset($ch = '')
    {
        if (!empty($ch))
        {
            $this->ch = $ch;
        }
        curl_reset($this->ch);
        return $this;
    }

    /**
     * 
     * @param resource $ch
     * @return \Curl
     */
    public function pause($ch = '')
    {
        if (!empty($ch))
        {
            $this->ch = $ch;
        }
        curl_pause($this->ch);
        return $this;
    }

    /**
     * @access protected
     * 
     * @param string $method
     * @param string $url
     * @param variant $data
     * @param array $options
     * @return boolean
     */
    protected function __request($method = 'GET', $url = '', $data = '', $options = array())
    {
        if (empty($this->ch))
        {
            $this->init();
        }
        if (empty($url) OR empty($method))
        {
            return FALSE;
        }
        $method = strtoupper($method);
        if ($method !== 'GET')
        {
            if ($method == 'POST')
            {
                $this->opt(CURLOPT_POST);
            } else
            {
                $this->opt(CURLOPT_CUSTOMREQUEST, $method);
                $this->opt(CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: ' . $method));
            }
            if (!empty($data))
            {
                if (is_array($data) OR is_object($data))
                {
                    $data = http_build_query($data);
                }
                $this->opt(CURLOPT_POSTFIELDS, $data);
            }
        }
        if ($this->use_auth)
        {
            $this->opt(CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            $this->opt(CURLOPT_USERPWD, $this->username . ':' . $this->password);
        }
        $user_agent = (isset($this->server['HTTP_USER_AGENT'])) ? $this->server['HTTP_USER_AGENT'] : 'Curl';
        $this->opt(CURLOPT_URL, $url);
        $this->opt(CURLOPT_USERAGENT, $user_agent);
        $this->opt(CURLOPT_SSL_VERIFYPEER, FALSE);
        $this->opt(CURLOPT_RETURNTRANSFER);
        $this->opt(CURLOPT_FORBID_REUSE);
        $this->opt(CURLOPT_FRESH_CONNECT);
        $this->opt(CURLOPT_COOKIESESSION);
        /** Set in Loop (Accept Object Options) */
        if (!empty($options))
        {
            foreach ($options as $option => $value)
            {
                $this->opt($option, $value);
            }
        }
        $this->exec();
        return TRUE;
    }

    /**
     * 
     * @param string $url
     * @return response
     */
    public function get($url = '')
    {
        $this->__request('GET', $url);
        return $this->response;
    }

    /**
     * 
     * @param string $url
     * @param object|array $data
     * @param string $options
     * @return response
     */
    public function post($url = '', $data = '', $options = array())
    {
        $this->__request('POST', $url, $data, $options);
        return $this->response;
    }

    /**
     * 
     * @param string $url
     * @param object|array $data
     * @param array $options
     * @return response
     */
    public function put($url = '', $data = '', $options = array())
    {
        $this->__request('PUT', $url, $data, $options);
        return $this->response;
    }

    /**
     * 
     * @param string $url
     * @param object|array $data
     * @param array $options
     * @return response
     */
    public function patch($url = '', $data = '', $options = array())
    {
        $this->__request('PATCH', $url, $data, $options);
        return $this->response;
    }

    /**
     * 
     * @param string $url
     * @param object|array $data
     * @param array $options
     * @return response
     */
    public function delete($url = '', $data = '', $options = array())
    {
        $this->__request('DELETE', $url, $data, $options);
        return $this->response;
    }

    /**
     * 
     * @param string $url
     * @param object|array $data
     * @param array $options
     * @return response
     */
    public function link($url = '', $data = '', $options = array())
    {
        $this->__request('LINK', $url, $data, $options);
        return $this->response;
    }

    /**
     * 
     * @param string $url
     * @param object|array $data
     * @param array $options
     * @return response
     */
    public function unlink($url = '', $data = '', $options = array())
    {
        $this->__request('UNLINK', $url, $data, $options);
        return $this->response;
    }

    /**
     * 
     * @param string $url
     * @param object|array $data
     * @param array $options
     * @return response
     */
    public function lock($url = '', $data = '', $options = array())
    {
        $this->__request('LOCK', $url, $data, $options);
        return $this->response;
    }

    /**
     * 
     * @param string $url
     * @param object|array $data
     * @param array $options
     * @return response
     */
    public function propfind($url = '', $data = '', $options = array())
    {
        $this->__request('PROPFIND', $url, $data, $options);
        return $this->response;
    }

    /**
     * 
     * @param type $xml xmldata
     * @return json
     */
    public function xml2json($xml = '')
    {
        if ($xml !== '')
        {
            $xml = simplexml_load_string($xml);
        } else
        {
            $xml = array();
        }
        return json_encode($xml);
    }

}
