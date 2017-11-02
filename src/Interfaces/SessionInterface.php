<?php

namespace c0py\Wsman\Interfaces;

interface SessionInterface
{
    /**
     * Returns the current raw session.
     *
     * @return mixed
     */
    public function get();
    
	/**
     * Returns a new QueryBuilder instance.
     *
     * @return \Stevebauman\Wmi\Query\Builder
     */
    public function newQuery();
    
	/**
     * Runs the specified raw query on the current
     * connection and returns the result.
     *
     * @param string $query
     *
     * @return mixed
     */
    public function query($query);
}
