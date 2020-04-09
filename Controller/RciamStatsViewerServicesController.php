<?php
App::uses("StandardController","Controller");
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

  public function index() {
    //Get data if any for the configuration of RciamStatsViewer  
    $configData = $this->RciamStatsViewer->getConfiguration($this->cur_co['Co']['id']);
    
    $conn=$this->RciamStatsViewer->connect($this->cur_co['Co']['id']);
   
    $utils = new RciamStatsViewerUtils($configData);
    $vv_logincount_per_day = $utils->getLoginCountPerDay($conn,0);

    $vv_logincount_per_idp = $utils->getLoginCountPerIdp($conn,0);

    $vv_logincount_per_sp = $utils->getLoginCountPerSp($conn,0);
    
    // Return the existing data if any
    $this->set('vv_logincount_per_sp', $vv_logincount_per_sp);
    $this->set('vv_logincount_per_idp', $vv_logincount_per_idp);
    $this->set('vv_logincount_per_day', $vv_logincount_per_day);
    $this->set('rciam_stats_viewers', $configData);
    // $this->set('vv_conn',$conn);
  }

  function isAuthorized() {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $roles = $this->Role->calculateCMRoles();
  
    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();
  
    // Determine what operations this user can perform
    $p['index'] = ($roles['cmadmin'] || $roles['coadmin']);
    $this->set('permissions', $p);
    
    return($p[$this->action]);
  }
}