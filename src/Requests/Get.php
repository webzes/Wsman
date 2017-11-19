<?php
namespace c0py\Wsman\Requests;

use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;

class Get {

    protected $resourceUri;
    protected $selectors;
    protected $action = 'http://schemas.xmlsoap.org/ws/2004/09/transfer/Get';
    
    public function __construct($resourceUri, $selectors = []) {
        $this->resourceUri = $resourceUri;
        $this->selectors = $selectors;
    }
    
    public function request() {
        $client = new Client([
            'base_uri' => 'xxx',
            'auth' => [
                    'xxx', 
                    'xxx'
                ],
            'timeout'  => 2.0,
        ]);

        try {
            $response = $client->post(
            '/wsman',
                [
                    'body'    => $xmlRequest,
                    'headers' => [
                        'Content-Type' => 'application/soap+xml;charset=UTF-8',
                        'SOAPAction' => $action
                    ]
                ]
            );

            //var_dump($response);
        } catch (\Exception $e) {
            echo 'Exception:' . $e->getMessage();
        }

        if ($response->getStatusCode() === 200) {
            // Success!
            $xmlResponse = simplexml_load_string($response->getBody());
        } else {
            echo 'Response Failure !!!';
        }
    }
}
