<?php
App::uses("StandardController","Controller");

class RciamStatsViewersController extends StandardController
{
  // Class name, used by Cake
  public $name = "RciamStatsViewers";
  
  // This controller needs a CO to be set
  public $requires_co = true;
  
  public $uses = array(
    "RciamStatsViewer.RciamStatsViewer",
    "Co",
  );

  public function edit($id=null) {
    $configData = $this->RciamStatsViewer->getConfiguration($this->cur_co['Co']['id']);
    
    $id = isset($configData['RciamStatsViewer']) ? $configData['RciamStatsViewer']['id'] : -1;
    
    if($this->request->is('post')) {
      // We're processing an update
      // if i had already set edit before, now retrieve the entry and update
      if($id > 0){
        $this->RciamStatsViewer->id = $id;
        $this->request->data['RciamStatsViewer']['id'] = $id;
      }
      
      try {
        /*
         * The check of the fields' values happen in two phases.
         * 1. The framework is responsible to ensure the presentation of all the keys
         * everytime i make a post. We achieve this by setting the require field to true.
         * 2. On the other hand not all fields are required to have a value for all cases. So we apply logic and apply the notEmpty logic
         * in the frontend through Javascript.
         * */
        $save_options = array(
          'validate'  => true,
        );
        
        if($this->RciamStatsViewer->save($this->request->data, $save_options)){
          $this->Flash->set(_txt('rs.saved'), array('key' => 'success'));
        } else {
          $invalidFields = $this->RciamStatsViewer->invalidFields();
          $this->log(__METHOD__ . "::exception error => ".print_r($invalidFields, true), LOG_DEBUG);
          $this->Flash->set(_txt('rs.rciam_stats_viewer.error'), array('key' => 'error'));
        }
      }
      catch(Exception $e) {
        $this->log(__METHOD__ . "::exception error => ".$e, LOG_DEBUG);
        $this->Flash->set($e->getMessage(), array('key' => 'error'));
      }
      // Redirect back to a GET
      $this->redirect(array('action' => 'edit', 'co' => $this->cur_co['Co']['id']));
    } else {
      
     $this->set('vv_stats_type_list', RciamStatsViewerStatsTypeEnum::type);
      // Return the settings
      $this->set('rciam_stats_viewers', $configData);
    }
  }

  /**
   * For Models that accept a CO ID, find the provided CO ID.
   * - precondition: A coid must be provided in $this->request (params or data)
   *
   * @since  COmanage Registry v2.0.0
   * @return Integer The CO ID if found, or -1 if not
   */

  public function parseCOID($data = null) {
    if($this->action == 'edit') {
      if(isset($this->request->params['named']['co'])) {
        return $this->request->params['named']['co'];
      }
    }
    
    return parent::parseCOID();
  }

  /**
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for auth decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v2.0.0
   * @return Array Permissions
   */

  function isAuthorized() {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $roles = $this->Role->calculateCMRoles();
  
    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();
  
    // Determine what operations this user can perform
    $p['edit'] = ($roles['cmadmin'] || $roles['coadmin']);
    $this->set('permissions', $p);
    
    return($p[$this->action]);
  }
}