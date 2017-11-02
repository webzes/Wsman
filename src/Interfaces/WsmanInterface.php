<?php

namespace c0py\Wsman\Interfaces;

interface WsmanInterface
{
    public function __construct($host, $username, $password);
    
	public function connect($namespace);
    
	public function getSession();
    
	public function getHost();
    
	public function getUsername();
    
	public function setHost($host);
    
	public function setUsername($username);
    
	public function setPassword($password);
}
