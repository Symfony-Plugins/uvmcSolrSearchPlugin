<?php

/**
 * Reset the index of solr.
 *
 * @package uvmcSolrSearchPlugin
 * @subpackage task
 * @author Marc Weistroff <mweistroff@uneviemoinschere.com>
 * @version $Id$
 */
class resetIndexTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    // // add your own arguments here
     $this->addArguments(array(
       new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
     ));

    $this->addOptions(array(
     // new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', null),
      // add your own options here
    ));

    $this->namespace        = 'uvmc-solr';
    $this->name             = 'reset-index';
    $this->briefDescription = 'reset the solr index';
    $this->detailedDescription = <<<EOF
The [reset-index|INFO] deletes the entire solr index.
Call it with:

  [php symfony uvmc-solr:reset-index application|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $confirm = $this->askConfirmation(array(sprintf('resetIndex will DELETE the index of this service: http://%s:%s%s.',
                                                    sfConfig::get('uvmc_solr_service_host'),
                                                    sfConfig::get('uvmc_solr_service_port'),
                                                    sfConfig::get('uvmc_solr_service_path')),
                                           'Are you sure you want to proceed? (y/N)'),
                                      'QUESTION',
                                      false);
    if(!$confirm)
    {
      $this->logSection('uvmc-solr', 'task aborted');
      return 1;
    }
    
    $solr = uvmcSolrServicesManager::getInstance()->getService();
    $solr->deleteByQuery('*:*');
    $this->logSection('uvmc-solr', 'Index has been deleted');
  }
}