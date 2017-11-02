<?php

namespace c0py\wsman

use COM;
use c0py\Wsman\Interfaces\WsmanInterface;

class Wsman implements WsmanInterface
{
    protected $host;
    protected $username;
    protected $password;
    protected $session;
    protected $com;
    protected $script = 'Wsman.Automation';
	
    public function __construct($host = 'localhost', $username = null, $password = null, $com = null)
    {
        $this->com = $com ? new COM($this->script);
        $this->setHost($host);
        $this->setUsername($username);
        $this->setPassword($password);
    }
	
    public function connect()
    {
        $connectionOptions = $this->com->CreateConnectionOptions;
        $connectionOptions->UserName = $this->getUsername();
        $connectionOptions->Password = $this->password;

        //$flags = $this->com->SessionFlagUseBasic;
        //$flags = $this->com->SessionFlagUseKerberos;
        $flags = $this->com->SessionFlagCredUserNamePassword;

        $session = $this->com->CreateSession($this->getHost(), $flags, $connectionOptions);
        if ($session) {

            // Set the session
            $this->setSession(new Session($session));
            return $this->session;
        }
        return false;
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

    /* ALIASES
    wmi      = http://schemas.microsoft.com/wbem/wsman/1/wmi
    wmicimv2 = http://schemas.microsoft.com/wbem/wsman/1/wmi/root/cimv2
    cimv2    = http://schemas.dmtf.org/wbem/wscim/1/cim-schema/2
    winrm    = http://schemas.microsoft.com/wbem/wsman/1
    wsman    = http://schemas.microsoft.com/wbem/wsman/1
    shell    = http://schemas.microsoft.com/wbem/wsman/1/windows/shell
    */

    //$response = $session->Get("http://schemas.microsoft.com/wbem/wsman/1/wmi/root/cimv2/Win32_Service?Name=Spooler");
    //$response = $session->Get("http://schemas.microsoft.com/wbem/wsman/1/wmi/root/cimv2/Win32_OperatingSystem");
    //$response = $session->Get("winrm/config");

    /* Enumerate Properties = ($uri, $filter, $dialect, $flags) */
    $filter = "SELECT * FROM Win32_NetworkAdapterConfiguration WHERE IPEnabled = true";
    $dialect = "http://schemas.microsoft.com/wbem/wsman/1/WQL";
    $response = $session->Enumerate("wmi/root/cimv2/*", $filter, $dialect);

    /* Enumerate Responses */
    $results = [];
    while(!$response->AtEndOfStream) {
        $item = simplexml_load_string($response->ReadItem());
        $namespaces = $item->getNamespaces(true);
        //var_dump($namespaces);
        $results[] = $item->children($namespaces["p"]);
    }
    print_r($results);

    public function transform($item)
    {
        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $item);
        $xml = new SimpleXMLElement(utf8_encode($response));
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);
        return $responseArray;
    }
    /* END Enumerate Responses */


    /* GET Responses
        $item = simplexml_load_string($response);
        $namespaces = $item->getNamespaces(true);
        var_dump($namespaces);
        $results[] = $item->children($namespaces["p"]);
        print_r($results);
    */
	
	
	
    /**
     * Returns the current session to the host.
     *
     * @return bool|Session
     */
    public function getSession()
    {
        if ($this->session instanceof SessionInterface) {
            return $this->session;
        }
        return false;
    }
    /**
     * Returns the current host to connect to.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
    /**
     * Returns the current username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    /**
     * Sets the current connection.
     *
     * @param ConnectionInterface $connection
     *
     * @return $this
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
        return $this;
    }
    /**
     * Sets the host to connect to.
     *
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = (string) $host;
        return $this;
    }
    /**
     * Sets the current username.
     *
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = (string) $username;
        return $this;
    }
    /**
     * Sets the current password.
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = (string) $password;
        return $this;
    }
}
