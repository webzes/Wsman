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
    
	/**
     * Returns the current raw COM session.
     *
     * @return mixed
     */
    public function get()
    {
        return $this->session;
    }

    /**
     * Executes the specified query on the current session.
     *
     * @param string $query
     *
     * @return mixed
     */
    public function query($query)
    {
        return $this->session->ExecQuery($query);
    }
    
}
