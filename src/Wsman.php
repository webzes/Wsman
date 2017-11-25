<?php
namespace c0py\Wsman;

use SoapClient;
use c0py\Wsman\Request;

class Wsman extends SoapClient
{
    /**
    * @var array
    */
    protected $options;

    /**
    * @var string
    */
    protected $requestXml;

    public function __construct($options = [])
    {
        $this->options = $options;
        //uri required by SoapClient in nonWSDL mode so we send empty string
        $this->options['uri'] = '';

        parent::__construct(null, $this->options);
    }

    public function identify()
    {
      $request = new Request('Identify', $this->options);
      $this->requestXml = $request->build();
      return $this->__soapCall('identify', []);
    }

    public function get($resourceUri, $params = [])
    {
        $request = new Request('Get', $this->options, $resourceUri, $params);
        $this->requestXml = $request->build();
        return $this->__soapCall('get', []);
    }

    /* need to finish Pull looping as only returning 51 results */
    public function enumerate($resourceUri)
    {
      $request = new Request('Enumerate', $this->options, $resourceUri);
      $this->requestXml = $request->build();
      $response = $this->__soapCall('enumerate', []); //should return a UUID

      $items = [];
      while( is_array($response) AND array_key_exists('EnumerationContext', $response) ) {
        $response = $this->pull($resourceUri, $response['EnumerationContext']);
        $results = current( (array)$response['Items'] );
        $results = array_map(function($o){return (array)$o;}, $results);

        array_push( $items, $results );
      }

      $allItems = array_merge(...$items);
      return $allItems;
    }

    private function pull($resourceUri, $uuid)
    {
      $request = new Request('Pull', $this->options, $resourceUri, $uuid);
      $this->requestXml = $request->build();
      return $this->__soapCall('pull', []);
    }

    public function put()
    {
      //TODO
    }

    public function invoke($command, $resourceUri, $params = [])
    {
      $request = new Request('Invoke', $this->options, $resourceUri, $params, $command);
      $this->requestXml = $request->build();
      return $this->__soapCall('invoke', []);
    }

    public function __doRequest($request, $location, $action, $version, $one_way = false)
    {
        $this->__last_request = $this->requestXml;

        $handle = curl_init($this->options['location']);

        $credentials = $this->options['login'] . ':' . $this->options['password'];
        $headers = [
            'Method: POST',
            'User-Agent: PHP-SOAP-CURL',
            'Content-Type: application/soap+xml; charset=utf-8',
            //'SOAPAction: "' . $action . '"'
        ];

        curl_setopt($handle, CURLINFO_HEADER_OUT, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $this->requestXml);
        curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        // Authentication
        //curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
        curl_setopt($handle, CURLOPT_USERPWD, $credentials);

        $response = curl_exec($handle);

        return $response;
    }
}
