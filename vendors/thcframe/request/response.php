<?php

namespace THCFrame\Request;

use THCFrame\Core\Base;
use THCFrame\Request\Exception;

/**
 * Class accepts a response constructor option, which is the result of an HTTP request. 
 * It splits this response string into headers and a body, which are available through getter
 * methods
 */
class Response extends Base
{

    protected $_response;

    /**
     * @read
     */
    protected $_body = null;

    /**
     * @readwrite
     */
    protected $_data = array();

    /**
     * @read
     */
    protected $_headers = array();

    /**
     * @readwrite
     */
    protected $_httpVersionStatusHeader;

    /**
     * 
     * @param type $method
     * @return \THCFrame\Request\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf('%s method not implemented', $method));
    }

    /**
     * Object constructor
     * 
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (!empty($options['response'])) {
            $response = $this->_response = $options['response'];
            unset($options['response']);
        }

        parent::__construct($options);

        if (isset($response) && !empty($response)) {
            $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
            preg_match_all($pattern, $response, $matches);

            $headers = array_pop($matches[0]);
            $headers = explode(PHP_EOL, str_replace('\r\n\r\n', '', trim($headers)));

            $this->_body = str_replace($headers, '', $response);

            $version = array_shift($headers);
            $this->setHttpVersionStatusHeader($version);

            foreach ($headers as $header) {
                preg_match('#(.*?)\:\s?(.*)#', $header, $matches);
                $this->setHeader($matches[1], $matches[2]);
            }
        }
    }

    /**
     * 
     * @return type
     */
    public function __toString()
    {
        if (!empty($this->_data) && $this->_body !== null) {
            $this->setHeader('Content-type', 'application/json');
            return json_encode(array('body' => $this->_body, 'data' => $this->_data));
        } elseif (!empty($this->_data) && $this->_body === null) {
            $this->setHeader('Content-type', 'application/json');
            return json_encode($this->_data);
        } elseif (empty($this->_data) && $this->_body !== null) {
            $this->setHeader('Content-type', 'text/html');
            return $this->_body;
        } else {
            return null;
        }
    }

    /**
     * Set header
     * 
     * @param string $type
     * @param string $content
     * @return \THCFrame\Request\Response
     */
    public function setHeader($type, $content)
    {
        if (!isset($this->_headers[$type]) || $this->_headers[$type] != $content) {
            $this->_headers[$type] = $content;
        }

        return $this;
    }

    /**
     * 
     * @param type $versionHeader
     * @return \THCFrame\Request\Response
     */
    public function setHttpVersionStatusHeader($versionHeader)
    {
        $this->_httpVersionStatusHeader = $versionHeader;

        return $this;
    }

    /**
     * Check if headers can be send
     * 
     * @return boolean
     */
    public function canSendHeaders()
    {
        if (headers_sent()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Send headers
     */
    public function sendHeaders()
    {
        if ($this->canSendHeaders() && !empty($this->_headers)) {
            header($this->getHttpVersionStatusHeader());

            foreach ($this->_headers as $type => $content) {
                header("{$type}: {$content}");
            }
        }

        return $this;
    }

    public function send($useExit = true)
    {
        echo $this->__toString();

        if ($useExit) {
            exit;
        }
    }

    /**
     * 
     * @param string $body
     */
    public function setBody($body)
    {
        $this->_body = $body;

        return $this;
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @return \THCFrame\Request\Response
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $this->_data += $key;
        } else {
            $this->_data[$key] = $value;
        }

        return $this;
    }

    /**
     * Prepend string to body
     * 
     * @param string $string
     */
    public function prependBody($string)
    {
        $this->_body = $string . $this->_body;
    }

    /**
     * Append string to body
     * 
     * @param string $string
     */
    public function appendBody($string)
    {
        $this->_body .= $string;
    }

    /**
     * Return response status message by status code
     * 
     * @param int $code
     * @return string
     */
    public function getStatusMessageByCode($code = 200)
    {
        $httpCodes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
        );
        
        return $httpCodes[(int)$code];
    }

}
