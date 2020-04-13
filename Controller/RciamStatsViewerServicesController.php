<?php
App::uses("StandardController", "Controller");
require('/srv/comanage/comanage-registry-Fix_regex_PCRE_group_error/local/Plugin/RciamStatsViewer/Lib/utils.php');
class RciamStatsViewerServicesController extends StandardController
{
  // Class name, used by Cake
  public $name = "RciamStatsViewerServices";

  public $requires_co = true;

  public $uses = array(
    "RciamStatsViewer.RciamStatsViewer",
    "Co",
    "RciamStatsViewer.RciamStatsViewerUtils"
  );
  private $utils;

  public function __construct ($request, $response){
    parent::__construct($request, $response);
    $co=$request->params['named']['co'];
    $configData = $this->RciamStatsViewer->getConfiguration($request->params['named']['co']);
    $this->utils = new RciamStatsViewerUtils($configData);
  } 

  public function index()
  {
    //Get data if any for the configuration of RciamStatsViewer  
    $conn = $this->RciamStatsViewer->connect($this->cur_co['Co']['id']);

    //$this->utils = new RciamStatsViewerUtils($configData);
    $vv_logincount_per_day = $this->utils->getLoginCountPerDay($conn, 0);
    $vv_totalloginscount_today = $this->utils->getTotalLoginCounts($conn, 1);

    $vv_totalloginscount = array(
      $this->utils->getTotalLoginCounts($conn, 1),
      $this->utils->getTotalLoginCounts($conn, 7),
      $this->utils->getTotalLoginCounts($conn, 30),
      $this->utils->getTotalLoginCounts($conn, 365)
    );

    $vv_logincount_per_idp = $this->utils->getLoginCountPerIdp($conn, 0);

    $vv_logincount_per_sp = $this->utils->getLoginCountPerSp($conn, 0);

    // Return the existing data if any
    $this->set('vv_totalloginscount', $vv_totalloginscount);
    $this->set('vv_logincount_per_sp', $vv_logincount_per_sp);
    $this->set('vv_logincount_per_idp', $vv_logincount_per_idp);
    $this->set('vv_logincount_per_day', $vv_logincount_per_day);
    // $this->set('vv_conn',$conn);
  }
  public function getlogincountperidpperday()
  {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $this->autoRender = false; // We don't render a view in this example
    $this->request->onlyAllow('ajax'); // No direct access via browser URL
    $this->layout = null;
    $days = $this->request->query['days'];
    $identifier = (isset($this->request->query['identifier']) ? $this->request->query['identifier'] : null);
    $type = (isset($this->request->query['type']) && $this->request->query['type'] != '' ? $this->request->query['type'] : null);
    $conn = $this->RciamStatsViewer->connect($this->request->params['named']['co']);
    
    if ($type == null) {
      $vv_logincount_per_day_range = $this->utils->getLoginCountPerDay($conn, $days);
      $vv_logincount_idp_per_day = $this->utils->getLoginCountPerIdp($conn, $days);
      $vv_logincount_sp_per_day = $this->utils->getLoginCountPerSp($conn, $days);
      $vv_logincount_per_day['range'] = $vv_logincount_per_day_range;
      $vv_logincount_per_day['idps'] = $vv_logincount_idp_per_day;
      $vv_logincount_per_day['sps'] = $vv_logincount_sp_per_day;
    } else if ($type == "idp") {
      $vv_logincount_per_day_range = $this->utils->getLoginCountPerDayForIdp($conn, $days, $identifier);
      $vv_logincount_per_day['sps'] = $this->utils->getLoginCountPerSp($conn, $days, $identifier);
      $vv_logincount_per_day['range'] = $vv_logincount_per_day_range;

      // $vv_logincount_idp_per_day = $this->utils->getLoginCountPerDayForIdp($conn, $days, $identifier);    
    }
    else if ($type == "sp") {
      $vv_logincount_per_day_range = $this->utils->getLoginCountPerDayForSp($conn, $days, $identifier);
      $vv_logincount_per_day['idps'] = $this->utils->getLoginCountPerIdp($conn, $days, $identifier);
      $vv_logincount_per_day['range'] = $vv_logincount_per_day_range;

    }

    $this->response->type('json');
    $this->response->statusCode(201);
    $this->response->body(json_encode($vv_logincount_per_day));
    return $this->response;
  }
  
  public function getdataforsp()
  {
    $sp = $this->request->query['sp'];
    $this->autoRender = false; // We don't render a view in this example
    $this->layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
    $conn = $this->RciamStatsViewer->connect($this->request->params['named']['co']);
 
    $vv_totalloginscount = array(
      $this->utils->getTotalLoginCounts($conn, 1, $sp),
      $this->utils->getTotalLoginCounts($conn, 7, $sp),
      $this->utils->getTotalLoginCounts($conn, 30, $sp),
      $this->utils->getTotalLoginCounts($conn, 365, $sp)
    );
    $this->set('vv_totalloginscount', $vv_totalloginscount);

    $this->response->type('json');
    $this->response->statusCode(201);
    $this->response->body(json_encode($vv_totalloginscount));
    return $this->response;
  }

  public function getdataforidp()
  {
    $idp = $this->request->query['idp'];
    $this->autoRender = false; // We don't render a view in this example
    $this->layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
    $conn = $this->RciamStatsViewer->connect($this->request->params['named']['co']);

    $vv_totalloginscount = array(
      $this->utils->getTotalLoginCounts($conn, 1, null, $idp),
      $this->utils->getTotalLoginCounts($conn, 7, null, $idp),
      $this->utils->getTotalLoginCounts($conn, 30, null, $idp),
      $this->utils->getTotalLoginCounts($conn, 365, null, $idp)
    );
    $this->set('vv_totalloginscount', $vv_totalloginscount);

    $this->response->type('json');
    $this->response->statusCode(200);
    $this->response->body(json_encode($vv_totalloginscount));
    return $this->response;
  }

  public function getchartforsp()
  {
    $sp = $this->request->query['sp'];
    $days = (isset($this->request->query['days']) ? $this->request->query['days'] : 0);
    $this->autoRender = false; // We don't render a view in this example
    $this->layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
    $conn = $this->RciamStatsViewer->connect($this->request->params['named']['co']);

    $vv_logincounts['idp'] = $this->utils->getAccessCountForServicePerIdentityProviders($conn, $days, $sp);
    $vv_logincounts['sp'] = $this->utils->getLoginCountPerDayForSp($conn, $days, $sp);

    $this->response->type('json');
    $this->response->statusCode(200);
    $this->response->body(json_encode($vv_logincounts));
    return $this->response;
  }

  public function getchartforidp()
  {
    $idp = $this->request->query['idp'];
    $days = (isset($this->request->query['days']) ? $this->request->query['days'] : 0);
    $this->autoRender = false; // We don't render a view in this example
    $this->layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
    $conn = $this->RciamStatsViewer->connect($this->request->params['named']['co']);

    $vv_logincounts['sp'] = $this->utils->getAccessCountForIdentityProviderPerServiceProviders($conn, $days, $idp);
    $vv_logincounts['idp'] = $this->utils->getLoginCountPerDayForIdp($conn, $days, $idp);
    $this->response->type('json');
    $this->response->statusCode(200);
    $this->response->body(json_encode($vv_logincounts));
    return $this->response;
  }


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

  function isAuthorized()
  {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $roles = $this->Role->calculateCMRoles();

    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();

    // Determine what operations this user can perform
    $p['index'] = ($roles['cmadmin'] || $roles['coadmin']);
    $p['getdataforsp'] = ($roles['cmadmin'] || $roles['coadmin']);
    $p['getchartforsp'] = ($roles['cmadmin'] || $roles['coadmin']);
    $p['getchartforidp'] = ($roles['cmadmin'] || $roles['coadmin']);
    $p['getlogincountperidpperday'] = ($roles['cmadmin'] || $roles['coadmin']);
    $this->set('permissions', $p);

    return ($p[$this->action]);
  }
}
