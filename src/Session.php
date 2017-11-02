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
     * Executes the specified query on the current connection.
     *
     * @param string $query
     *
     * @return mixed
     */
    public function query($query)
    {
        return $this->session->ExecQuery($query);
    }
    
	/**
     * Returns a new query builder instance.
     *
     * @return Builder
     */
    public function newQuery()
    {
        return new Builder($this, new Grammar());
    }
    
	/**
     * Handle dynamic method calls on the query builder object.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->newQuery(), $method], $parameters);
    }
}
