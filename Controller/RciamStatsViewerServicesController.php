<?php
App::uses('StandardController', 'Controller');

class RciamStatsViewerServicesController extends StandardController
{
  // Class name, used by Cake
  public $name = 'RciamStatsViewerServices';

  public $requires_co = true;

  public $uses = array(
    'RciamStatsViewer.RciamStatsViewer',
    'Co',
    'RciamStatsViewer.RciamStatsViewerUtils'
  );
  private $utils;
  
  /**
   * __construct
   *
   * @param  mixed $request
   * @param  mixed $response
   * @return void
   */
  
  public function __construct($request, $response)
  {
    parent::__construct($request, $response);
    $co = $request->params['named']['co'];
    $configData = $this->RciamStatsViewer->getConfiguration($request->params['named']['co']);
    $this->utils = new RciamStatsViewerUtils($configData);
  }
  
  /**
   * index page
   *
   * @return void
   */
  
  public function index()
  {
    $fail = false;
    try {
      // Try to connect to the database
      $conn = $this->RciamStatsViewer->connect($this->cur_co['Co']['id']);

      // Fetch the data
      $vv_logincount_per_day = ($this->utils->getLoginCountPerDay($conn, 0)) ?: array();

      $vv_totalloginscount = array(
        $this->utils->getTotalLoginCounts($conn, 1),
        $this->utils->getTotalLoginCounts($conn, 7),
        $this->utils->getTotalLoginCounts($conn, 30),
        $this->utils->getTotalLoginCounts($conn, 365)
      );

      $vv_logincount_per_idp = ($this->utils->getLoginCountPerIdp($conn, 0)) ?: array();
      $vv_logincount_per_sp = ($this->utils->getLoginCountPerSp($conn, 0)) ?: array();

      // Return the existing data if any
      $this->set('vv_totalloginscount', $vv_totalloginscount);
      $this->set('vv_logincount_per_sp', $vv_logincount_per_sp);
      $this->set('vv_logincount_per_idp', $vv_logincount_per_idp);
      $this->set('vv_logincount_per_day', $vv_logincount_per_day);
      
    } catch (MissingConnectionException $e) {
      $this->log(__METHOD__ . ':: Database Connection failed. Error Message::' . $e->getMessage(), LOG_DEBUG);
      $this->Flash->set(_txt('er.rciam_stats_viewer.db.connect', array($e->getMessage())), array('key' => 'error'));
      $fail = true;
    } catch (InvalidArgumentException $e) {
      $this->Flash->set(_txt('er.rciam_stats_viewer.db.action', array($e->getMessage())), array('key' => 'error'));
      $fail = true;
    } catch (RuntimeException $e) {
      $this->Flash->set(_txt('er.rciam_stats_viewer.db.action', array($e->getMessage())), array('key' => 'error'));
      $fail = true;
    } finally {
      if($fail) {
        // Initialize frontend placeholders
        $this->set('vv_totalloginscount', array());
        $this->set('vv_logincount_per_sp', array());
        $this->set('vv_logincount_per_idp', array());
        $this->set('vv_logincount_per_day', array());
      }
    }
  }

    
  /**
   * Get data for summary tab or Idp/Sp Details Tabs depending
   * on days user selected.
   *
   * @return CakeResponse
   */

  public function getlogincountperday()
  {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    $this->autoRender = false; // We don't render a view
    $this->request->onlyAllow('ajax'); // No direct access via browser URL
    $this->layout = null;
    $days = intVal($this->request->query['days']);
    $identifier = (isset($this->request->query['identifier']) ? $this->request->query['identifier'] : null);
    $type = (isset($this->request->query['type']) && $this->request->query['type'] != '' ? $this->request->query['type'] : null);
    $conn = $this->RciamStatsViewer->connect($this->request->params['named']['co']);

    if ($type === null) {
      $vv_logincount_per_day_range = $this->utils->getLoginCountPerDay($conn, $days);
      $vv_logincount_idp_per_day = $this->utils->getLoginCountPerIdp($conn, $days);
      $vv_logincount_sp_per_day = $this->utils->getLoginCountPerSp($conn, $days);
      $vv_logincount_per_day['range'] = $vv_logincount_per_day_range;
      $vv_logincount_per_day['idps'] = $vv_logincount_idp_per_day;
      $vv_logincount_per_day['sps'] = $vv_logincount_sp_per_day;
    } else if ($type === "idp") {
      $vv_logincount_per_day_range = $this->utils->getLoginCountPerDayForIdp($conn, $days, $identifier);
      $vv_logincount_per_day['sps'] = $this->utils->getLoginCountPerSp($conn, $days, $identifier);
      $vv_logincount_per_day['range'] = $vv_logincount_per_day_range;
    } else if ($type === "sp") {
      $vv_logincount_per_day_range = $this->utils->getLoginCountPerDayForSp($conn, $days, $identifier);
      $vv_logincount_per_day['idps'] = $this->utils->getLoginCountPerIdp($conn, $days, $identifier);
      $vv_logincount_per_day['range'] = $vv_logincount_per_day_range;
    }

    $this->response->type('json');
    $this->response->statusCode(201);
    $this->response->body(json_encode($vv_logincount_per_day));
    return $this->response;
  }
  
  /**
   *  Get data for specific service provider
   *
   * @return CakeResponse
   */

  public function getdataforsp()
  {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $sp = $this->request->query['sp'];
    $days = (isset($this->request->query['days']) ? $this->request->query['days'] : 0);
    $this->autoRender = false; // We don't render a view 
    $this->layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
    $conn = $this->RciamStatsViewer->connect($this->request->params['named']['co']);

    $vv_totalloginscount = array(
      $this->utils->getTotalLoginCounts($conn, 1, $sp),
      $this->utils->getTotalLoginCounts($conn, 7, $sp),
      $this->utils->getTotalLoginCounts($conn, 30, $sp),
      $this->utils->getTotalLoginCounts($conn, 365, $sp)
    );
    $vv_logincounts['tiles'] = $vv_totalloginscount;
    $vv_logincounts['idp'] = $this->utils->getAccessCountForServicePerIdentityProviders($conn, $days, $sp);
    $vv_logincounts['sp'] = $this->utils->getLoginCountPerDayForSp($conn, $days, $sp);

    $this->response->type('json');
    $this->response->statusCode(200);
    $this->response->body(json_encode($vv_logincounts));
    return $this->response;
  }
  
  /**
   * Get data for specific identity provider
   *
   * @return CakeResponse
   */

  public function getdataforidp()
  {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $idp = $this->request->query['idp'];
    $days = (isset($this->request->query['days']) ? $this->request->query['days'] : 0);
    $this->autoRender = false; // We don't render a view
    $this->layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
    $conn = $this->RciamStatsViewer->connect($this->request->params['named']['co']);

    $vv_totalloginscount = array(
      $this->utils->getTotalLoginCounts($conn, 1, null, $idp),
      $this->utils->getTotalLoginCounts($conn, 7, null, $idp),
      $this->utils->getTotalLoginCounts($conn, 30, null, $idp),
      $this->utils->getTotalLoginCounts($conn, 365, null, $idp)
    );

    $vv_logincounts['sp'] = $this->utils->getAccessCountForIdentityProviderPerServiceProviders($conn, $days, $idp);
    $vv_logincounts['idp'] = $this->utils->getLoginCountPerDayForIdp($conn, $days, $idp);
    $vv_logincounts['tiles'] = $vv_totalloginscount;

    $this->response->type('json');
    $this->response->statusCode(200);
    $this->response->body(json_encode($vv_logincounts));
    return $this->response;
  }
  
  /**
   * beforeRender
   *
   * @return void
   */
  
  public function beforeRender()
  {
    parent::beforeRender();
    $tab_settings["idps"] = array(
      'prefix' => 'idp',
      'ctpName' => 'tab',      
    );
    $tab_settings["sps"] = array(
      'prefix' => 'sp',
      'ctpName' => 'tab',      
    );
    $this->set('vv_tab_settings', $tab_settings);
  }
    
  /**
   * beforeFilter
   *
   * @return void
   */
  public function beforeFilter()
  {
    // For ajax i accept only json format
    if ($this->request->is('ajax')) {
      $this->RequestHandler->addInputType('json', array('json_decode', true));
      $this->Security->validatePost = false;
      $this->Security->enabled = true;
      $this->Security->csrfCheck = false;
    } else
      parent::beforeFilter();
  }

  /**
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for auth decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v2.0.0
   * @return Array Permissions
   */

  function isAuthorized()
  {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $roles = $this->Role->calculateCMRoles();

    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();

    // Determine what operations this user can perform
    $p['index'] = ($roles['cmadmin'] || $roles['coadmin']);
    $p['getdataforsp'] = ($roles['cmadmin'] || $roles['coadmin']);
    $p['getdataforidp'] = ($roles['cmadmin'] || $roles['coadmin']);
    $p['getlogincountperday'] = ($roles['cmadmin'] || $roles['coadmin']);
    $this->set('permissions', $p);

    return ($p[$this->action]);
  }
}
