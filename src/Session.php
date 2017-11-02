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
    Create	    - Creates a new instance of a resource and returns the URI of the new object.
    Delete	    - Deletes the resource specified in the resource URI.
    Enumerate	- Enumerates a collection, table, or message log resource.
    Get	        - Retrieves a resource from the service and returns an XML representation of the current instance of the resource.
    Identify	- Queries a remote computer to determine if it supports the WS-Management protocol
    Invoke	    - Invokes a method that returns the results of the method call.
    Put	        - Updates a resource.
    */
    
    //$query = "winrm/config";
    //$query = "http://schemas.microsoft.com/wbem/wsman/1/wmi/root/cimv2/Win32_Service?Name=Spooler";
    //$query = "http://schemas.microsoft.com/wbem/wsman/1/wmi/root/cimv2/Win32_OperatingSystem";
    public function get($query)
    {
        $response = $this->session->Get($query);
        $item = simplexml_load_string($response);
        $namespaces = $item->getNamespaces(true);
        $results = $item->children(reset($namespaces));
        return $results;
    }
    
    //$query = "wmi/root/cimv2/*"
    //$filter = "SELECT * FROM Win32_NetworkAdapterConfiguration WHERE IPEnabled = true";
    //$dialect = "http://schemas.microsoft.com/wbem/wsman/1/WQL";
    public function enumerate($query, $filter, $dialect, $flags)
    {
        $response = $this->session->Enumerate($query, $filter, $dialect);
        
        $results = [];
        while(!$response->AtEndOfStream) {
            $item = simplexml_load_string($response->ReadItem());
            $namespaces = $item->getNamespaces(true);
            $results[] = $item->children(reset($namespaces));
        }
        return $results;
    }
    
}
