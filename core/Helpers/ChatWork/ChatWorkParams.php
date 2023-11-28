<?php

namespace Core\Helpers\ChatWork;

class ChatWorkParams extends ChatWorkValidator
{
    protected $arg = [];

    public function __construct($args = [])
    {
        $this->arg = (is_object($args)) ? get_object_vars($args) : (array)$args;
    }

    /**
     * Add parameter
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function add($key, $value)
    {
        $this->arg[$key] = $value;
        return $this;
    }

    /**
     * Remove parameter
     * @param string $key
     * @return $this
     */
    public function remove($key)
    {
        if (isset($this->arg[$key])) {
            unset($this->arg[$key]);
        }

        return $this;
    }

    /**
     * Get parameter value
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->{$key};
    }

    /**
     * Format Query string paramter
     * @param array $ignores
     * @return string
     */
    public function toURIParams($ignores = [])
    {
        $param = array();
        foreach ($this->arg as $key => $value) {
            if ($value !== '' && !in_array($key, $ignores)) {
                $param[] = rawurlencode($key) . '=' . rawurlencode($value);
            }
        }

        return implode('&', $param);
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return (isset($this->arg[$name])) ? $this->arg[$name] : null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->add($name, $value);
    }
}
