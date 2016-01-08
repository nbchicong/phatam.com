<?php
/**
 * Parses the response from a Curl request into an object containing
 * the response body and an associative array of headers
 *
 * @package curl
 * @author Sean Huber <shuber@huberry.com>
**/
class MyCurlFile {
    
    /**
     * The body of the response without the headers block
     *
     * @var string
    **/
    public $fileName = '';
    public $filePath = '';
    public $fileSize = 0;
    
    /**
     * Accepts the result of a curl request as a string
     *
     * <code>
     * $response = new CurlResponse(curl_exec($curl_handle));
     * echo $response->body;
     * echo $response->headers['Status'];
     * </code>
     *
     * @param string $response
    **/
    function __construct($fileName, $filePath) {
        $this->fileName = $fileName;
        $this->filePath = $filePath;
        if (is_readable($filePath)) {
            $this->fileSize = filesize($filePath);
        }

    }
    
    /**
     * Returns the response body
     *
     * <code>
     * $curl = new Curl;
     * $response = $curl->get('google.com');
     * echo $response;  # => echo $response->body;
     * </code>
     *
     * @return string
    **/
    function __toString() {
        return $this->body;
    }
    
}