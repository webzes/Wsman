<?php
namespace c0py\Wsman;

class Wsman
{
    protected $url;
    protected $username;
    protected $password;
    protected $auth;
	
    public function __construct($target = 'localhost', $username = null, $password = null, $auth = 'plaintext')
    {
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setAuthentication($auth);
        $this->setUrl($target, $this->auth);
    }
	
    /**
     * Returns the current URL.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
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
     * Returns the current username.
     *
     * @return string
     */
    public function getAuth()
    {
        return $this->auth;
    }
    
    /**
     * Sets the URL to connect to.
     *
     * @param string $target, $transport
     *
     * @return $this
     */
    public function setUrl($target, $auth)
    {
        $this->url = (string) $this->buildUri($target, $auth);
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
    
    /**
     * Sets the auth method.
     *
     * @param string $auth
     *
     * @return $this
     */
    public function setAuthentication($auth)
    {
        $this->auth = (string) $auth;
        return $this;
    }
	
    private function buildUri($target, $auth)
    {
        $targetParts = parse_url($target);

        if (isset($targetParts['scheme'])) {
            $scheme = $targetParts['scheme'];
        }
        else {
            $scheme = ($transport == 'ssl') ? 'https' : 'http';
        }

        $host = $targetParts['host'];

        if (isset($targetParts['port'])) {
            $port = $targetParts['port'];
        }
        else {
            $port = ($transport == 'ssl') ? 5986 : 5985;
        }

        if (isset($targetParts['path'])) {
            $path = $targetParts['path'];
            $path = ltrim($path, '/');
        }
        else {
            $path = 'wsman';
        }
        $ret = $scheme."://".$host.":".$port."/".$path;
        
        return $ret;
    }

}
