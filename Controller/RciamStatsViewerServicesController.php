<?php
App::uses("StandardController","Controller");

class RciamStatsViewerServicesController extends StandardController
{
  // Class name, used by Cake
  public $name = "RciamStatsViewerServices";

  public $requires_co = true;

  public function index() {
    
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