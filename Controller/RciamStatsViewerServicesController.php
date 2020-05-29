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
    'CoPerson',
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
      $vv_logincount_per_day = ($this->utils->getLoginCountPerDayForProvider($conn, 0)) ?: array();

      $vv_totalloginscount = array(
        $this->utils->getTotalLoginCounts($conn, 1),
        $this->utils->getTotalLoginCounts($conn, 7),
        $this->utils->getTotalLoginCounts($conn, 30),
        $this->utils->getTotalLoginCounts($conn, 365)
      );
      
      $vv_logincount_per_idp = ($this->utils->getLoginCountPerIdp($conn, 0)) ?: array();
      $vv_logincount_per_sp = ($this->utils->getLoginCountPerSp($conn, 0)) ?: array();
      $vv_logincount_per_month = ($this->utils->getLoginCountByRanges($conn, null, null, 'init')) ?: array();
      // Return the existing data if any
      $this->set('vv_totalloginscount', $vv_totalloginscount);
      $this->set('vv_logincount_per_sp', $vv_logincount_per_sp);
      $this->set('vv_logincount_per_idp', $vv_logincount_per_idp);
      $this->set('vv_logincount_per_day', $vv_logincount_per_day);
      $this->set('vv_logincount_per_month', $vv_logincount_per_month);

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
      if ($fail) {
        // Initialize frontend placeholders
        $this->set('vv_totalloginscount', array());
        $this->set('vv_logincount_per_sp', array());
        $this->set('vv_logincount_per_idp', array());
        $this->set('vv_logincount_per_day', array());
        $this->set('vv_logincount_per_month',array());
      }
    }
  }


  /**
   * getdataforuserstiles
   *
   * @return void
   */

  public function getdataforuserstiles()
  {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    $this->autoRender = false; // We don't render a view
    $this->request->onlyAllow('ajax'); // No direct access via browser URL
    $this->layout = null;

    $data = [];
    //last year
    $data[] = $this->CoPerson->find('count', array(
      'conditions' => array(
        'CoPerson.co_person_id' => NULL,
        'CoPerson.deleted' => false,
        'CoPerson.co_id' => $this->request->params['named']['co'],
        'CoPerson.status' => 'A',
      ),
    ));
    //last 7 days 
    $data[] = $this->CoPerson->find('count', array(
      'conditions' => array(
        'CoPerson.co_person_id' => NULL,
        'CoPerson.deleted' => false,
        'CoPerson.co_id' => intVal($this->request->params['named']['co']),
        'CoPerson.status' => 'A',
        'CoPerson.created > CURRENT_DATE - INTERVAL \'7 days\'',
      ),
    ));
    //last 30 days
    $data[] = $this->CoPerson->find('count', array(
      'conditions' => array(
        'CoPerson.co_person_id' => NULL,
        'CoPerson.deleted' => false,
        'CoPerson.co_id' => intVal($this->request->params['named']['co']),
        'CoPerson.status' => 'A',
        'CoPerson.created > CURRENT_DATE - INTERVAL \'30 days\'',
      ),
    ));
    //last year
    $data[] = $this->CoPerson->find('count', array(
      'conditions' => array(
        'CoPerson.co_person_id' => NULL,
        'CoPerson.deleted' => false,
        'CoPerson.co_id' => intVal($this->request->params['named']['co']),
        'CoPerson.status' => 'A',
        'CoPerson.created > CURRENT_DATE - INTERVAL \'1 year\'',
      ),
    ));
    $this->response->type('json');
    $this->response->statusCode(201);
    $this->response->body(json_encode($data));
    return $this->response;
  }

  /**
   * getdataforcolumnschart
   *
   * @return void
   */

  public function getdataforcolumnchart()
  {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    $this->autoRender = false; // We don't render a view
    $this->request->onlyAllow('ajax'); // No direct access via browser URL
    $this->layout = null;
    $co_id = $this->request->params['named']['co'];
    $range = $this->request->query['range'];
    $tab = $this->request->query['tab'];
    if($tab == null || $tab == 'registered'){
      $table = 'cm_co_people';
      $tableColumn = 'co_person_id';
      $status = 'AND status=\'A\'';
      $selectExtra = '';
      $whereExtra = '';
    }
    else {
      $table = 'cm_cous';
      $tableColumn = 'cou_id';
      $status = '';
      $selectExtra = ", string_agg(name,', ') as names, string_agg(to_char(created, 'YYYY-MM-DD'),', ') as created_date, string_agg(description,'|| ') as description";
      $whereExtra = " AND parent_id IS NULL ";
    }
    if (RciamStatsViewerDateTruncEnum::type[$range] === RciamStatsViewerDateTruncEnum::monthly) {
      $sql = "select count(*), date_trunc( 'month', created ) as range_date, min(created) as min_date $selectExtra from $table where $tableColumn IS NULL AND NOT DELETED AND co_id=$co_id $status $whereExtra AND created >
      date_trunc('month', CURRENT_DATE) - INTERVAL '1 year' group by date_trunc( 'month', created ) ORDER BY date_trunc( 'month', created ) ASC";
    } 
    else if (RciamStatsViewerDateTruncEnum::type[$range] === null || RciamStatsViewerDateTruncEnum::type[$range]  == RciamStatsViewerDateTruncEnum::yearly)
      $sql = "select count(*), date_trunc( 'year', created ) as range_date, min(created) as min_date $selectExtra from $table where $tableColumn IS NULL AND NOT DELETED AND co_id=$co_id $status $whereExtra group by date_trunc( 'year', created ) ORDER BY date_trunc( 'year', created ) ASC";
    else if (RciamStatsViewerDateTruncEnum::type[$range] == RciamStatsViewerDateTruncEnum::weekly)
      $sql = "select count(*), date_trunc( 'week', created ) as range_date, min(created) as min_date $selectExtra from $table where $tableColumn IS NULL AND NOT DELETED AND co_id=$co_id $status $whereExtra AND created >
      date_trunc('month', CURRENT_DATE) - INTERVAL '6 months' group by date_trunc( 'week', created ) ORDER BY date_trunc( 'week', created ) ASC";

    $data = $this->RciamStatsViewer->query($sql);

    $this->response->type('json');
    $this->response->statusCode(201);
    $this->response->body(json_encode($data));
    return $this->response;
  }


  /**
   * getdatafordatatable
   *
   * @return void
   */

  function getdatafordatatable()
  {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    $this->autoRender = false; // We don't render a view
    $this->request->onlyAllow('ajax'); // No direct access via browser URL
    $this->layout = null;
    $dateFrom = $this->request->query['dateFrom'];
    $dateTo = $this->request->query['dateTo'];
    $type = $this->request->query['type'];
    $identifier = (isset($this->request->query['identifier']) && $this->request->query['identifier'] != "") ? $this->request->query['identifier'] : null;
    $co_id = $this->request->params['named']['co'];
    $groupBy = $this->request->query['groupBy'];

    $data = [];
    if ($dateFrom != null && $dateTo != null && $dateTo > $dateFrom) {

      if ($type === null || $type === 'registered' || $type === 'cou') {
        if (RciamStatsViewerDateTruncEnum::type[$groupBy] !== null)
          $trunc_by = RciamStatsViewerDateTruncEnum::type[$groupBy];
        else
          $trunc_by = RciamStatsViewerDateTruncEnum::monthly;
          
        if($type == null || $type == 'registered'){
            $table = 'cm_co_people';
            $tableColumn = 'co_person_id';
            $status = 'AND status=\'A\'';
            $selectExtra = "";
            $whereExtra = '';
          }
          else {
            $table = 'cm_cous';
            $tableColumn = 'cou_id';
            $status = '';
            $selectExtra = ", string_agg(name,', ') as names, string_agg(to_char(created, 'YYYY-MM-DD'),', ') as created_date, string_agg(description,'|| ') as description";
            $whereExtra = ' AND parent_id IS NULL ';
          }
        $sql = "select count(*), date_trunc('" . $trunc_by . "', created) as range_date, date_trunc('" . $trunc_by . "', created) as show_date, min(created) as min_date $selectExtra from $table where $tableColumn IS NULL AND NOT DELETED AND co_id=" . $co_id . " $status $whereExtra AND  created BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "' group by date_trunc('" . $trunc_by . "',created)";
        $data = $this->RciamStatsViewer->query($sql);
      }   
      else 
      {
        $fail = false;
        try {
          // Try to connect to the database
          $conn = $this->RciamStatsViewer->connect($co_id);
          if ($type === 'idp' || $type === 'spSpecific') {
            $data["idps"] = $this->utils->getLoginCountPerIdp($conn, 0, $identifier, $dateFrom, $dateTo);
          } else if ($type === 'sp' || $type === 'idpSpecific') {
            $data["sps"] = $this->utils->getLoginCountPerSp($conn, 0, $identifier, $dateFrom, $dateTo);
          }
          else if ($type === 'dashboard') 
          {
            $data = $this->utils->getLoginCountByRanges($conn, $dateFrom, $dateTo, $groupBy);
          }
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
          if ($fail) {
            // Initialize frontend placeholders
            $data["sps"] = [];
            $data["idps"] = [];
            $data = [];
          }
        }
      }
    }
    $this->response->type('json');
    $this->response->statusCode(201);
    $this->response->body(json_encode($data));
    return $this->response;
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
    $co_id = $this->request->params['named']['co'];
    try {
      $fail = false;
      $conn = $this->RciamStatsViewer->connect($co_id);

      $days = intVal($this->request->query['days']);
      $identifier = (isset($this->request->query['identifier']) ? $this->request->query['identifier'] : null);
      $type = (isset($this->request->query['type']) && $this->request->query['type'] != '' ? $this->request->query['type'] : null);

      if ($type === null) { //Dashboard Summary
        $vv_logincount_per_day['range'] = $this->utils->getLoginCountPerDayForProvider($conn, $days);
        $vv_logincount_per_day['idps'] = $this->utils->getLoginCountPerIdp($conn, $days);
        $vv_logincount_per_day['sps'] = $this->utils->getLoginCountPerSp($conn, $days);
        
        $dateTo = date("Y-m-d");
        if($days === 365){
          $dateFrom = date('Y-m-d', strtotime('-364 days'));
          $groupBy = 'monthly';
        }
        else if($days === 30){
          $dateFrom = date('Y-m-d', strtotime('-29 days'));
          $groupBy = 'daily';
        }
        else if($days === 7) {
          $dateFrom = date('Y-m-d', strtotime('-6 days'));
          $groupBy = 'daily';
        }
        else if($days === 1){
          $dateFrom = date('Y-m-d', strtotime('-0 days'));
          $groupBy = 'daily';
        }
        $vv_logincount_per_day['datatable'] = $this->utils->getLoginCountByRanges($conn, $dateFrom, $dateTo, $groupBy);
      } 
      else if ($type === "idp") {
        $vv_logincount_per_day['range'] = $this->utils->getLoginCountPerDayForProvider($conn, $days, $identifier, $type);
        $vv_logincount_per_day['sps'] = $this->utils->getLoginCountPerSp($conn, $days, $identifier);
      } else if ($type === "sp") {
        $vv_logincount_per_day['range'] = $this->utils->getLoginCountPerDayForProvider($conn, $days, $identifier, $type);
        $vv_logincount_per_day['idps'] = $this->utils->getLoginCountPerIdp($conn, $days, $identifier);
      }
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
      if ($fail) {
        // Initialize frontend placeholders
        $vv_logincount_per_day['range'] = [];
        $vv_logincount_per_day['idps'] = [];
        $vv_logincount_per_day['sps'] = [];
      }
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
    $vv_logincounts['sp'] = $this->utils->getLoginCountPerDayForProvider($conn, $days, $sp, "sp");

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
    $vv_logincounts['idp'] = $this->utils->getLoginCountPerDayForProvider($conn, $days, $idp, "idp");
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
    $tab_settings["registered"] = array(
      'prefix' => 'registered',
      'ctpName' => 'tab',
    );
    $tab_settings["cou"] = array(
      'prefix' => 'cou',
      'ctpName' => 'cou',
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
   * @since  COmanage Registry v3.1.x
   * @return Array Permissions
   */

  function isAuthorized()
  {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $roles = $this->Role->calculateCMRoles();

    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();

    // Determine what operations this user can perform
    $p['index'] = ($roles['comember'] || $roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin']);
    $p['getdataforsp'] = ($roles['comember'] || $roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin']);
    $p['getdataforidp'] = ($roles['comember'] || $roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin']);
    $p['getlogincountperday'] = ($roles['comember'] || $roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin']);
    $p['getdataforcolumnchart']  = ($roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin']);

    // Tab Permissions
    $p['idp'] = ($roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin']);
    $p['sp'] = ($roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin']);
    $p['registered'] = ($roles['cmadmin'] || $roles['coadmin']);
    $p['cou'] = ($roles['cmadmin'] || $roles['coadmin']);
    $this->set('vv_permissions', $p);

    return ($p[$this->action]);
  }
}
