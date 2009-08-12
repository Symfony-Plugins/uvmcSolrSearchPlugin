<?php

/**
 * @package uvmcSolrSearchPlugin
 * @subpackage event
 * @author Marc Weistroff <mweistroff@uneviemoinschere.com>
 * @version $Id$
 */
class uvmcSolrEventListener
{
  /**
   * listen to symfony's admin generator "admin.save_object" event.
   * If the model has a method 'getSolrDocumentFields', then the model will be indexed
   *
   * @param sfEvent $event 
   */
  static public function listenToAdminSaveObject(sfEvent $event)
  {
    $model = $event['object'];

    if (method_exists($model, 'getSolrDocumentFields'))
    {
      $docFields = $model->getSolrDocumentFields();
      $doc = uvmcSolrEventListener::transformArrayToSolrDocument($docFields);

      $solr = uvmcSolrServicesManager::getInstance()->getService();
      $solr->addDocument($doc);
      $solr->commit();
    }
  }


  /**
   * Listen to symfony's admin generator "admin.delete_object" event.
   *
   * @param sfEvent $event
   */
  static function listentoAdminDeleteObject(sfEvent $event)
  {
    $model = $event['object'];
    if (method_exists($model, 'getSolrDocumentFields'))
    {
      $docFields = $model->getSolrDocumentFields();
      $id = $docFieds['id'];

      $solr = uvmcSolrServicesManager::getInstance()->getService();
      $solr->deleteById($id);
    }
  }


  /**
   * Listen to uvmc_solr.delete_documment
   *
   * Parameters:
   *   - object (object, required)
   *     The object to delete from index.
   * 
   * @param sfEvent $request
   */
  static function listenToDeleteDocument(sfEvent $request)
  {
    $model = $event['object'];

    $docFields = $model->getSolrDocumentFields();
    $solr = uvmcSolrServicesManager::getInstance()->getService();
    $solr->deleteById($docFields['id']);
  }


  /**
   * Listen to uvmc_solr.add_document event
   * Available parameters:
   *   - object (object, required)
   *      The object to index.
   * 
   *   - commit (bool, optional).
   *      false by default.
   *      set it to true to commit after the document has been added.
   * @param sfEvent $event
   */
  static public function listenToAddDocument(sfEvent $event)
  {
    $model = $event['object'];

    $docFields = $model->getSolrDocumentFields();
    $doc = uvmcSolrEventListener::transformArrayToSolrDocument($docFields);

    $solr = uvmcSolrServicesManager::getInstance()->getService();
    $solr->addDocument($doc);

    $commit = isset($event['commit']) ? $event['commit'] : false;
    if ($commit)
    {
      $solr->commit();
    }
  }
  

  /**
   * Commit all the document added to the master service
   * 
   * @param sfEvent $event
   */
  static public function listenToCommit(sfEvent $event)
  {
    $solr = uvmcSolrServicesManager::getInstance()->getService();
    $solr->commit();
  }


  /**
   * Search the Solr service
   * Available parameters:
   *   - query (string, required)
   *     Search query to send to the solr service
   *
   *   - offset (integer, optional)
   *     0 by default.
   *     Offset of the first result to be returned. 
   *
   *   - maxhit (integer, optional)
   *     Maximum number of results to return. 10 by default
   *
   * @param sfEvent $event
   */
  static public function listenToSearch(sfEvent $event)
  {
    if (!isset($event['query']))
      throw new sfException('Please specify a query.');
      
    $query = $event['query'];
    $offset = isset($event['offset']) ? $event['offset'] : 0;
    $maxhit = isset($event['maxhit']) ? $event['maxhit'] : 10;

    $solr = uvmcSolrServicesManager::getInstance()->getService();
    $results = $solr->search($query, $offset, $maxhit);

    $event->setReturnValue($results);
  }


  /**
   * Transform an array into a solr document
   * @param array $docArray
   * @return Apache_Solr_Document instance
   */
  static public function transformArrayToSolrDocument($docArray)
  {
    $document = new Apache_Solr_Document();

    foreach ($docArray as $fieldName => $fieldProperty)
    {
      if (!is_array($fieldProperty))
      {
        $document->addField($fieldName, $fieldProperty);
      }
      else
      {
        if (isset($fieldProperty['value']))
        {
          $boost = isset($fieldProperty['boost']) ? (float) $fieldProperty['boost'] : false;
          $document->addField($fieldName, $fieldProperty['value'], $boost);
        }
        else
        {
          foreach ($fieldProperty as $property)
          {
            $boost = isset($property['boost']) ? (float) $property['boost'] : false;
            $document->setMultiValue($fieldName, $property['value'], false);
          }
        }
      }
    }

    return $document;
  }
}