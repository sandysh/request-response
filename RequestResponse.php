<?php
/* 
    Package: Request Response for CURL
    Author: Sandesh Satyal
*/
namespace App\Services;

class RequestResponse
{
    public $url;
    
    public $curl;

    public $body;
    
    public $info;
    
    public $method;

    public $response;

    public $response_headers;

    function __construct()
    {
        
    }

    public function request($url)
    {
        $this->url = $url;
        $curl = curl_init($this->url); 
        curl_setopt($curl, CURLOPT_FAILONERROR, true); 
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($curl, CURLOPT_HEADER, true);
        $body = curl_exec($curl);
        
        if(curl_error($curl)){
            $this->setBody(curl_error($curl), $headerSize=NULL);
        } else {
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

            $this->setResponseHeaders($body, $headerSize);

            $this->setBody($body, $headerSize);
            $this->body = json_decode($this->body);
        }

        curl_close($curl);
        
        return [
            'body' => $this->body,
            'headers' => $this->response_headers
        ];
    }

    public function setCurlOptions($curl, $params=[], $query=[], $headers=[])
    {
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if(!empty($params)){
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }
        if(!empty($headers)){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
    }


    /* 
        $query = http_build_query([
        'page' => '3',
        'name' => 'sandesh'
        ]);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer c27e8af4adf34c2eb9e97a5ab3aa9a2d'
        ];
    */

    public function get($url, $query=[], $headers = [])
    {
        $this->url = $url;
        $curl = !empty($query) ? curl_init($this->url.'?'.$query) : curl_init($this->url);
        $this->setCurlOptions($curl, $params=[] ,$query, $headers);
        $body = curl_exec($curl);
        return $this->processRequest($body, $curl);
    }


    /* 
        $params = [
        'name' => 'sandesh',
        'job' => 'developer',
        ];
    */

    public function post($url, $params, $headers=[])
    {
        $this->url = $url;
        $curl = curl_init($this->url); 
        $this->setCurlOptions($curl, $params, $query, $headers);
        $body = curl_exec($curl);
        return $this->processRequest($body, $curl);
    }

    public function processRequest($body, $curl)
    {
        if(curl_error($curl)){
            $this->setBody(curl_error($curl), $headerSize=NULL);
        } else {
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

            $this->setResponseHeaders($body, $headerSize);

            $this->setBody($body, $headerSize);
            $this->body = json_decode($this->body);
        }

        curl_close($curl);

        return [
            'body' => $this->body,
            'headers' => $this->response_headers
        ];
    }

    public function checkConnection()
    {
        $response = null;
        system("ping -c 1 google.com", $response);
        if($response == 1){
            return true;
        } else {
            return false;
        }
    }

    public function getHeaders($respHeaders) {
        $headers = array();
        $headerText = substr($respHeaders, 0, strpos($respHeaders, "\r\n\r\n"));
        foreach (explode("\r\n", $headerText) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    public function setResponseHeaders($body, $headerSize)
    {
        $header = substr($body, 0, $headerSize);
        $this->response_headers = $this->getHeaders($header);
    }

    public function setBody($body, $headerSize)
    {
        $this->body = substr($body, $headerSize);
    }

}


/* Example 

$reqres = new RequestResponse();

$query = http_build_query([
 'country' => 'us',
  'apiKey' => 'c27e8af4adf34c2eb9e97a5ab3aa9a2d',
 ]);
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer c27e8af4adf34c2eb9e97a5ab3aa9a2d'
];
$response =  $reqres->get('https://newsapi.org/v2/top-headlines',$query, $headers);

$params = [
    'name' => 'morpheus',
    'job' => 'leader',
];
$response =  $reqres->post('https://reqres.in/api/users',$params);

return $response;


*/