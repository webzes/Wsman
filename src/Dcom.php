<?php

namespace c0py\Wsman;

use COM;

class Dcom
{
    protected $host;
    protected $username;
    protected $password;
    protected $session;
    protected $com;
    protected $script = 'Wsman.Automation';
	
    public function __construct($host = 'localhost', $username = null, $password = null, $com = null)
    {
        $this->com = $com ?: new COM($this->script);
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
	
    public function createResourceLocator($uri) {
        return $this->com->CreateResourceLocator($uri);
    }

    /* ALIASES
    wmi      = http://schemas.microsoft.com/wbem/wsman/1/wmi
    wmicimv2 = http://schemas.microsoft.com/wbem/wsman/1/wmi/root/cimv2
    cimv2    = http://schemas.dmtf.org/wbem/wscim/1/cim-schema/2
    winrm    = http://schemas.microsoft.com/wbem/wsman/1
    wsman    = http://schemas.microsoft.com/wbem/wsman/1
    shell    = http://schemas.microsoft.com/wbem/wsman/1/windows/shell
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
