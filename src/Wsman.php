<?php
namespace c0py\Wsman;

use SoapClient;
use c0py\Wsman\Request;
use GuzzleHttp\Client;

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
        //default auth to basic if not specified
        $this->options['auth'] = array_key_exists('auth', $this->options) ? $this->options['auth'] : 'basic';
        //default timeout to 2.0 if not specified
        $this->options['timeout'] = array_key_exists('timeout', $this->options) ? $this->options['timeout'] : 2.0;
	    $this->options['proxy'] = array_key_exists('proxy', $this->options) ? $this->options['proxy'] : false;

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

    /* Enumeration Parameters

    //WQL Example
    $params = [
        'dialect' => 'WQL',
        'query' => 'select Availability from Win32_Processor'
    ];

    //Filter Example
    $params = [
        'dialect' => 'Filter',
        'filters' => [
            'Name' => 'Bob',
            'Age' => '23'
        ]
    ];
    */
    public function enumerate($resourceUri, $params = [])
    {
		$request = new Request('Enumerate', $this->options, $resourceUri, $params);
		$this->requestXml = $request->build();
		$response = $this->__soapCall('enumerate', []);

		$items = [];
        while( is_array($response) ) {
			if(!isset($response['Items'])) break;

			$results = current( (array)$response['Items'] );

			if(is_object($results)) {
				$items[] = [(array)$results];
			} else {
				$results = array_map(function($o){return (array)$o;}, (array)$results);
				array_push( $items, $results );
			}
			
			if(array_key_exists('EndOfSequence', $response)) break;

			if(!empty($response['EnumerationContext'])) {
				$response = $this->pull($resourceUri, $response['EnumerationContext']);
			}
		}

		if(!empty($items)) {
            return array_merge(...$items);
		}
		return false;
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

        $clientOptions = [];
        $postOptions = [];

        $clientOptions['base_uri'] = $this->options['location'];

        if($this->options['auth'] == 'negotiate') {
          $postOptions['curl'] = [
                    CURLOPT_HTTPAUTH => CURLAUTH_NEGOTIATE,
                    CURLOPT_USERPWD => ":"
                  ];
        } else {
          $clientOptions['auth'] = [
                    $this->options['login'],
                    $this->options['password'],
                    $this->options['auth']
                  ];
        }

        // hack
        if($this->options['auth'] == 'digest') {
          $postOptions['curl'][CURLOPT_COOKIEJAR] = tmpfile();
          $postOptions['curl'][CURLOPT_COOKIEFILE] = tmpfile();
        }

        $clientOptions['timeout'] = $this->options['timeout'];
		$clientOptions['http_errors'] = false;
        
        if($this->options['proxy']) {
            $clientOptions['proxy'] = $this->options['proxy'];
        }

        $client = new Client($clientOptions);

        $postOptions['body'] = $this->requestXml;
        $postOptions['headers'] = [
            'Content-Type'  => 'application/soap+xml;charset=UTF-8',
            'User-Agent'    => 'PHP-SOAP-CURL',
        ];

        $response = $client->post('/wsman', $postOptions);

        return $response->getBody()->getContents();
    }
}
