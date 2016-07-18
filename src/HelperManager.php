<?php

namespace Zend\Expressive\Phptal;

use Zend\Expressive\Phptal\Helper\HelperInterface;

class HelperManager
{
    /**
     * Collection of helpers
     *
     * @var HelperInterface[]
     */
    private $helpers = [];
    
    /**
     * Register helper
     * 
     * @param HelperInterface $helper
     * @throws Exception\BadFunctionCallException
     */
    public function registerHelper(HelperInterface $helper)
    {
        if (!is_callable($helper)) {
            throw new Exception\BadFunctionCallException(sprintf(
                'A non-callable helper "%s", was provided; expected a callable or instance of "%s"',
                is_object($helper) ? get_class($helper) : gettype($helper),
                HelperInterface::class
            ));
        }
        
        $this->helpers[$helper->getHelperName()] = $helper;
    }
    
    /**
     * Get helper by name
     * 
     * @param string $name
     * @return HelperInterface
     * @throws Exception\MissingHelperException
     */
    public function getHelper($name)
    {
        if (!isset($this->helpers[$name])) {
            throw new Exception\MissingHelperException(sprintf('Helper with name "%s" is not registered', $name));
        }
        return $this->helpers[$name];
    }
    /**
     * Magic overload: Proxy calls to helper container
     *
     * @param  string $method    method name
     * @param  array  $arguments arguments to pass
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(
            $this->getHelper($method),
            $arguments
        );
    }
}
