<?php
namespace c0py\Wsman;

use c0py\Wsman\Interfaces\SessionInterface;

class Session implements SessionInterface
{
    /**
     * The current session.
     *
     * @var mixed
     */
    protected $session;
    
    /**
     * Constructor.
     *
     * @param mixed $session
     */
    public function __construct($session)
    {
        $this->session = $session;
    }
    
    /* SESSION
    Method	    Description
    Create      - Creates a new instance of a resource and returns the URI of the new object.
    Delete      - Deletes the resource specified in the resource URI.
    Enumerate   - Enumerates a collection, table, or message log resource.
    Get         - Retrieves a resource from the service and returns an XML representation of the current instance of the resource.
    Identify    - Queries a remote computer to determine if it supports the WS-Management protocol
    Invoke      - Invokes a method that returns the results of the method call.
    Put         - Updates a resource.
    */
    
    public function get($query)
    {
        try {
            $response = $this->session->Get($query);
        } catch(\com_exception $e) {
            //TODO: handle error - show for now
            echo $e->getMessage();
            return false;
        }

        $item = simplexml_load_string($response);
        $namespaces = $item->getNamespaces(true);
        $results = $item->children(reset($namespaces));
        return json_decode(json_encode($results));
    }
    
    public function enumerate($query, $filter = '', $dialect = 'http://schemas.microsoft.com/wbem/wsman/1/WQL', $flags = false)
    {
        try {
            $response = $this->session->Enumerate($query, $filter, $dialect, $flags);
        } catch(\com_exception $e) {
            //TODO: handle error - show for now
            echo $e->getMessage();
            return false;
        }

        $results = [];
        $s=0;
        while(!$response->AtEndOfStream) {
            $item = simplexml_load_string( utf8_encode( $response->ReadItem() ) );
            $namespaces = $item->getNamespaces(true);
            $item->registerXPathNamespace('w','http://schemas.dmtf.org/wbem/wsman/1/wsman.xsd');

            $isSelector = $item->xpath("//w:SelectorSet/w:Selector");

            if($item->count() > 0) {
                $results[] = $item;
            } else {
                if($isSelector) {
                    $item->registerXPathNamespace('p',$namespaces['p']);
                    $nodes = $item->children(reset($namespaces));
                    $i=0;
                    foreach ($nodes as $node) {
                        $nodeQuery = $node->xpath("//w:SelectorSet/w:Selector");
                        $results[$s][$node->GetName()] = dom_import_simplexml($nodeQuery[$i])->textContent;
                        ++$i;
                    }

                } else {
                    $results[] = $item->children(reset($namespaces));
                }
            }
            ++$s;
        }
        return json_decode(str_replace(':{}',':null',json_encode($results)));
    }
}
