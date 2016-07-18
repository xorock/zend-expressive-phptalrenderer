<?php

namespace Zend\Expressive\Phptal;

use PHPTAL;
use Zend\Expressive\Template\ArrayParametersTrait;
use Zend\Expressive\Template\DefaultParamsTrait;
use Zend\Expressive\Template\TemplatePath;
use Zend\Expressive\Template\TemplateRendererInterface;

class PhptalRenderer implements TemplateRendererInterface
{
    use ArrayParametersTrait;
    use DefaultParamsTrait;
    
    /**
     * @var PHPTAL
     */
    private $template;
    
    /**
     * @var string
     */
    private $suffix;
    
    public function __construct(PHPTAL $template = null, $suffix = 'html')
    {
        if (null === $template) {
            $template = $this->createTemplate();
        }
        $this->template = $template;
        $this->suffix   = is_string($suffix) ? $suffix : 'html';
    }
    
    /**
     * Create a default PHPTAL engine
     *
     * @params string $path
     * @return PHPTAL
     */
    private function createTemplate()
    {
        return new PHPTAL();
    }

    /**
     * Add a template path to the engine.
     *
     * @param string $path
     * @param string $namespace
     */
    public function addPath($path, $namespace = null)
    {
        $repositories = $this->template->getTemplateRepositories();
        
        if (!in_array($path, $repositories)) {
            $this->template->setTemplateRepository($path);
        }        
    }

    /**
     * Retrieve configured paths from the engine.
     *
     * @return TemplatePath[]
     */
    public function getPaths()
    {
        $paths = [];
        
        foreach ($this->template->getTemplateRepositories() as $path) {
            $paths[] = new TemplatePath($path);
        }
        
        return $paths;
    }

    /**
     * Render a template, optionally with parameters.
     * 
     * @param string $name
     * @param array|object $params
     * @return string
     */
    public function render($name, $params = [])
    {
        // Merge parameters based on requested template name
        $params = $this->mergeParams($name, $this->normalizeParams($params));
        
        $name = $this->normalizeTemplate($name);
        $this->template->setTemplate($name);
        
        // Merge parameters based on normalized template name
        $params = $this->mergeParams($name, $params);
        
        foreach ($params as $key => $value) {
            $this->template->set($key, $value);
        }
        
        return $this->template->execute();
    }
    
    /**
     * Normalize namespaced template.
     *
     * Normalizes templates in the format "namespace::template" to
     * "@namespace/template".
     *
     * @param string $template
     * @return string
     */
    public function normalizeTemplate($template)
    {
        $template = preg_replace('#^([^:]+)::(.*)$#', '$1/$2', $template);
        if (! preg_match('#\.[a-z]+$#i', $template)) {
            return sprintf('%s.%s', $template, $this->suffix);
        }

        return $template;
    }

}
