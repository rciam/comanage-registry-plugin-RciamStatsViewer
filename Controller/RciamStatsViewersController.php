<?php
App::uses("StandardController","Controller");

class RciamStatsViewersController extends StandrardController
{
     // Class name, used by Cake
  public $name = "RciamStatsViewers";
  
  // This controller needs a CO to be set
  public $requires_co = true;
  
  public $uses = array(
    "RciamStatsViewer.RciamStatsViewer",
    "Co",
  );

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