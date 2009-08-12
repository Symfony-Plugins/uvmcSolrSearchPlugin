<?php

/**
 * uvmcSolrServicesManager implements the singleton design pattern.
 * This class is used to manage a solr service.
 *
 * @package uvmcSolrSearchPlugin
 * @subpackage listener
 * @author  Marc Weistroff <mweistroff@uneviemoinschere.com>
 * @version $Id$
 */
class uvmcSolrServicesManager
{
  static
    $instance = null;
    
  protected
    $service = null;

  /**
   * Return the master Solr Service.
   * @return Apache_Solr_Service
   */
  public function getService()
  {
    if (is_null($this->service))
    {
      $this->createService();
    }
    if (!$this->service->ping())
    {
      throw new sfException("Can't ping this Solr server");
    }

    return $this->service;
  }

  /**
   * Create the master Solr service
   * 
   * @return Apache_Solr_service
   */
  protected function createService()
  {
    $host = sfConfig::get('uvmc_solr_service_host', null);
    $port = sfConfig::get('uvmc_solr_service_port', 8983);
    $path = sfConfig::get('uvmc_solr_service_path', "/solr");

    if (!is_null($host))
    {
      $service = new Apache_Solr_Service($host, $port, $path);
      if ($service->ping())
      {
        $this->service = $service;
        return $this->service;
      }
    }

    throw new sfException("Can't create an Apache_Solr_Service with this configuration.");
  }
  
  /**
   * @return uvmcSolrServicesManager instance
   */
  static function getInstance()
  {
    if(!self::$instance instanceof uvmcSolrServicesManager)
    {
      self::createInstance();
    }
    return self::$instance;
  }

  static function createInstance()
  {
    self::$instance = new uvmcSolrServicesManager();
  }
}
