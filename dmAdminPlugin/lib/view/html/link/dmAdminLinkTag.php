<?php

class dmAdminLinkTag extends dmLinkTag
{
  protected
  $requestContext,
  $controller;
  
  public function __construct($resource, array $requestContext, sfWebController $controller)
  {
    $this->resource       = empty($resource) ? '@homepage' : $resource;
    $this->requestContext = $requestContext;
    $this->controller     = $controller;
    
    $this->initialize();
  }

  protected function getBaseHref()
  {
    if(is_string($this->resource))
    {
      if (strncmp($this->resource, 'app:', 4) === 0)
      {
        $type = 'uri';
        $app = substr($this->resource, 4);
        /*
         * A slug may be added to the app name, extract it
         */
        if ($slashPos = strpos($app, '/'))
        {
          $slug = substr($app, $slashPos);
          $app  = substr($app, 0, $slashPos);
        }
        else
        {
          $slug = '';
        }
        
        $resource = self::$dmContext->getAppUrl($app).$slug;
      }
      elseif ($this->resource{0} === '/')
      {
        $resource = $this->resource;
        /*
         * add relativeUrlRoot to absolute resource
         */
        if(($relativeUrlRoot = $this->requestContext['relative_url_root']) && (strpos($resource, $relativeUrlRoot) !== 0))
        {
          $resource = $relativeUrlRoot.$resource;
        }
      }
      elseif(strncmp($this->resource, '+/', 2) === 0)
      {
        $resource = substr($this->resource, 2);
      }
      else
      {
        $resource = $this->resource;
      }
    }

    elseif(is_array($this->resource))
    {
      if(isset($this->resource[1]) && is_object($this->resource[1]))
      {
        $resource =array(
          'sf_route' => $this->resource[0],
          'sf_subject' => $this->resource[1]
        );
      }
      else
      {
        $resource = $this->resource;
      }
    }

    elseif(is_object($this->resource) && $this->resource instanceof dmDoctrineRecord && ($module = $this->resource->getDmModule()))
    {
      $resource = array(
        'sf_route' => $module->getUnderscore().'_edit',
        'sf_subject' => $this->resource
      );
    }
    
    if(isset($resource))
    {
      return $this->controller->genUrl($resource);
    }

    throw new dmException('Can not find href for '. $this->resource);
  }

  protected function renderText()
  {
    if (empty($this->options['text']))
    {
      if(is_object($this->options['source']))
      {
        if($this->options['source'] instanceof DmPage)
        {
          $text = $this->options['source']->get('name');
        }
        else
        {
          $text = (string) $this->options['source'];
        }
      }
      else
      {
        $text = $this->getBaseHref();
      }
    }
    else
    {
      $text = $this->options['text'];
    }

    return $text;
  }

}