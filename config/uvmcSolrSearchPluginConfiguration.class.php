<?php

/**
 * @package     uvmcSolrSearchPlugin
 * @subpackage  configuration
 * @author      Marc Weistroff <mweistroff@uneviemoinschere.com>
 * @version     $Id$
 */
class uvmcSolrSearchPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    if($this->configuration instanceof sfApplicationConfiguration)
    {
      $configCache = $this->configuration->getConfigCache();
      $configCache->registerConfigHandler('config/solr.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'uvmc_solr_'));
      require_once($configCache->checkConfig('config/solr.yml'));
    }

    $isEnabled = sfConfig::get('uvmc_solr_enabled', false);
    $isEnabled = $isEnabled == true || $isEnabled == 'on' ? true : false;

    if ($isEnabled)
    {
      $this->connectEvents();
    }
  }

  /**
   * Connect events to listeners
   */
  public function connectEvents()
  {
    $this->dispatcher->connect('admin.save_object', array('uvmcSolrEventListener', 'listenToAdminSaveObject'));
    $this->dispatcher->connect('admin.delete_object', array('uvmcSolrEventListener', 'listenToAdminDeleteObject'));
    $this->dispatcher->connect('uvmc_solr.search', array('uvmcSolrEventListener', 'listenToSearch'));
    $this->dispatcher->connect('uvmc_solr.commit', array('uvmcSolrEventListener', 'listenToCommit'));
    $this->dispatcher->connect('uvmc_solr.add_document', array('uvmcSolrEventListener', 'listenToAddDocument'));
    $this->dispatcher->connect('uvmc_solr.update_document', array('uvmcSolrEventListener', 'listenToAddDocument'));
    $this->dispatcher->connect('uvmc_solr.delete_document', array('uvmcSolrEventListener', 'listenToDeleteDocument'));
  }
}