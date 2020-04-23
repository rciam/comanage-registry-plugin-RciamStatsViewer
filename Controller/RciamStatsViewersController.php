<?php
App::uses("StandardController","Controller");

class RciamStatsViewersController extends StandardController
{
  // Class name, used by Cake
  public $name = "RciamStatsViewers";
  
   /*
  * By default a new CSRF token is generated for each request, and each token can only be used once.
  * If a token is used twice, the request will be blackholed. Sometimes, this behaviour is not desirable,
  * as it can create issues with single page applications.
  * */
  public $components = array(
    'RequestHandler',
    'Security' => array(
      'csrfUseOnce' => false,
      'csrfExpires' => '+10 minutes'
  ));
  
  // This controller needs a CO to be set
  public $requires_co = true;
  
  public $uses = array(
    "RciamStatsViewer.RciamStatsViewer",
    "Co",
  );
  
  /**
   * Test DB connectivity
   *
   * @param null $id
   * @return CakeResponse|null
   */
  public function testconnection() {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $this->autoRender = false; // We don't render a view in this example
    $this->request->onlyAllow('ajax'); // No direct access via browser URL

    if( $this->request->is('ajax') && $this->request->is('post') ) {
      $this->layout=null;
      $db_config = $this->request->data;
      $db_config['datasource'] = 'Database/' . RciamStatsViewerDBDriverTypeEnum::type[$db_config['datasource']];
      try {
        // Try to connect to the database
        $conn = $this->RciamStatsViewer->connect($this->cur_co['Co']['id'], $db_config);
        $this->response->type('json');
        $status = 200;
        $response = array(
          'status' => 'success',
          'msg'    => _txt('rs.rciam_stats_viewer.db.connect')
        );
      } catch (MissingConnectionException $e) {
        // Currently Postgre driver of Cakephp wraps all errors of PDO into MissingConnectionException
        $this->log(__METHOD__ . ':: Database Connection failed. Error Message::' . $e->getMessage(), LOG_DEBUG);
        $status = 503;
        $response = array(
          'status' => 'error',
          'msg'    => _txt('er.rciam_stats_viewer.db.connect', array($e->getMessage()))
        );
      }
  
      $this->response->statusCode((int)$status);
      $this->response->body(json_encode($response));
      $this->response->type('json');
      return $this->response;
    }
  }
  
  /**
   * Callback before other controller methods are invoked or views are rendered.
   * - postcondition: post request should be of type ajax
   *
   * @since  RciamStatsViewer v1.0
   */
  public function beforeFilter() {
    // Since we're overriding, we need to call the parent to run the authz check
    parent::beforeFilter();
    // For ajax i accept only json format
    if( $this->request->is('ajax') ) {
      $this->RequestHandler->addInputType('json', array('json_decode', true));
      $this->Security->validatePost = false;
      $this->Security->enabled = true;
      $this->Security->csrfCheck = true;
    }
  }
    
  /**
   * Edit Rciam Stats Viewer Settings
   *
   * @param  $id
   * @return void
   */
  public function edit($id=null) {
    //Get data if any for the configuration of RciamStatsViewer  
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
    
      // Return the olist of persistent values
      $this->set('vv_encoding_list', RciamStatsViewerDBEncodingTypeEnum::type);

      // Return the olist of persistent values
      $this->set('vv_persistent_list', array(true => 'true', false => 'false'));

      // Return the list of stats type
      $this->set('vv_stats_type_list', RciamStatsViewerStatsTypeEnum::type);

      // Return the list of dbdriver type
      $this->set('vv_dbdriver_type_list', RciamStatsViewerDBDriverTypeEnum::type);

      // Return the existing data if any
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
    if($this->action == 'edit'
       || $this->action == 'testconnection') {
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
    $p['testconnection'] = true;
    $this->set('permissions', $p);
    
    return($p[$this->action]);
  }
}