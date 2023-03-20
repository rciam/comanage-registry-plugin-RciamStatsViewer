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
    'CoGroup',
    'Cou',
    'CoPersonRole',
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
    $this->utils = new RciamStatsViewerUtils($this->RciamStatsViewer->getConfiguration($request->params['named']['co']));
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
      if(!empty($map_dashboard = $this->findLoginsPerCountry(NULL, NULL, NULL, RciamStatsViewerDateTruncEnum::monthly))) {
        // group countries logins by month
        $map_dashboard_group_by_date = Hash::combine($map_dashboard, '{n}.{n}.country', '{n}.{n}', '{n}.{n}.month');
        // find sum country-based logins per date 
        foreach($map_dashboard_group_by_date as $key=>$value) {
          $sum = 0 ;
          foreach($value as $key_name => $country) {
            $map_dashboard_group_by_date[$key][$key_name]['count'] = $country['sum'];
            $sum += $country['sum'];
          }
          $counts_per_date[$key]['count'] = $sum;
        }
        // get overall number of logins (this data will be different from country-based as country tables were added after)
        $vv_logincount_per_month = ($this->utils->getLoginCountByRanges($conn)) ?: array();
        
        // we have to format data properly, and count unknown country-based logins
        foreach($vv_logincount_per_month as $key => $row) {
          if(!empty($map_dashboard_group_by_date[$row[0]['range_date']])) {
            $vv_logincount_per_month[$key][0]['countries'] = $map_dashboard_group_by_date[$row[0]['range_date']];
            $unknown_logins = $vv_logincount_per_month[$key][0]['count'] - $counts_per_date[$row[0]['range_date']]['count'];
            if($unknown_logins > 0) {
              $vv_logincount_per_month[$key][0]['countries']['Unknown'] = array('name' => 'Unknown', 'count' => $unknown_logins);
            }
          }
          else {
            $vv_logincount_per_month[$key][0]['countries']['Unknown'] = array('name' => 'Unknown', 'count' => $vv_logincount_per_month[$key][0]['count']); 
          }
        }
        $vv_logins_per_country = ($this->findLoginsPerCountry()) ? : array();
      }
      else {
        $vv_logincount_per_month = ($this->utils->getLoginCountByRanges($conn)) ?: array();
        $vv_logins_per_country = array();
      }
      

      // Get Status Enum for Users
      $vv_status_enum[StatusEnum::Active] = 'Active';
      $vv_status_enum[StatusEnum::GracePeriod] = 'Grace Period';
      $vv_status_enum[StatusEnum::Suspended] = 'Suspended';

      // Return the existing data if any
      $this->set('vv_totalloginscount', $vv_totalloginscount);
      $this->set('vv_logincount_per_sp', $vv_logincount_per_sp);
      $this->set('vv_logincount_per_idp', $vv_logincount_per_idp);
      $this->set('vv_logincount_per_day', $vv_logincount_per_day);
      $this->set('vv_logincount_per_month', $vv_logincount_per_month);
      $this->set('vv_logins_per_country', $vv_logins_per_country);
      $this->set('vv_status_enum', $vv_status_enum);
      $this->set('vv_country_table', !empty($this->utils->getStatisticsCountryTableName()) ? : null);
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
        $this->set('vv_logincount_per_month', array());
        $this->set('vv_status_enum', array());
      }
    }
  }

   /**
   *  Get data for tabs when switching unique logins checkbox
   *
   * @return CakeResponse
   */

  public function getdatafortabs()
  {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    
    $unique_logins = isset($this->request->query['unique_logins']) && $this->request->query['unique_logins'] == "true" ? true : false;
    $this->autoRender = false; // We don't render a view 
    $this->layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
    $conn = $this->RciamStatsViewer->connect($this->request->params['named']['co']);
    $vv_logincounts['dashboard'] = $unique_logins ? $this->getUniqueLoginCountPerDayForProvider(0) : ($this->utils->getLoginCountPerDayForProvider($conn, 0));
    $vv_totalloginscount = ($unique_logins) ?
      array(
        $this->getTotalUniqueLoginCounts($conn, 1),
        $this->getTotalUniqueLoginCounts($conn, 7),
        $this->getTotalUniqueLoginCounts($conn, 30),
        $this->getTotalUniqueLoginCounts($conn, 365)
      ) : array(
        $this->utils->getTotalLoginCounts($conn, 1),
        $this->utils->getTotalLoginCounts($conn, 7),
        $this->utils->getTotalLoginCounts($conn, 30),
        $this->utils->getTotalLoginCounts($conn, 365)
      );
    $vv_logincounts['tiles'] = $vv_totalloginscount;

    $vv_logincounts['idp'] = $unique_logins ? $this->getUniqueAccessCountForServicePerIdentityProviders($conn, 0) : $this->utils->getLoginCountPerIdp($conn, 0);
    $vv_logincounts['sp']  = $unique_logins ? $this->getUniqueAccessCountForIdentityProviderPerServiceProviders($conn, 0) : $this->utils->getLoginCountPerSp($conn, 0);

    if(!empty($map_dashboard = $this->findLoginsPerCountry(NULL, NULL, NULL, RciamStatsViewerDateTruncEnum::monthly, $unique_logins))) {
      // group countries logins by month
      $map_dashboard_group_by_date = Hash::combine($map_dashboard, '{n}.{n}.country', '{n}.{n}', '{n}.{n}.month');
      // find sum country-based logins per date 
      foreach($map_dashboard_group_by_date as $key=>$value) {
        $sum = 0 ;
        foreach($value as $key_name => $country) {
          $map_dashboard_group_by_date[$key][$key_name]['count'] = $country['sum'];
          $sum += $country['sum'];
        }
        $counts_per_date[$key]['count'] = $sum;
      }
      // get overall number of logins (this data will be different from country-based as country tables were added after)
      $vv_logincount_per_month = ( $unique_logins ? $this->getUniqueLoginCountByRanges() : $this->utils->getLoginCountByRanges($conn) ) ?: array();
      
      // we have to format data properly, and count unknown country-based logins
      foreach($vv_logincount_per_month as $key => $row) {
        if(!empty($map_dashboard_group_by_date[$row[0]['range_date']])) {
          $vv_logincount_per_month[$key][0]['countries'] = $map_dashboard_group_by_date[$row[0]['range_date']];
          $unknown_logins = $vv_logincount_per_month[$key][0]['count'] - $counts_per_date[$row[0]['range_date']]['count'];
          if($unknown_logins > 0) {
            $vv_logincount_per_month[$key][0]['countries']['Unknown'] = array('name' => 'Unknown', 'count' => $unknown_logins);
          }
        }
        else {
          $vv_logincount_per_month[$key][0]['countries']['Unknown'] = array('name' => 'Unknown', 'count' => $vv_logincount_per_month[$key][0]['count']); 
        }
      }
      $vv_logins_per_country = ($this->findLoginsPerCountry(NULL, NULL, NULL, NULL, $unique_logins)) ? : array();
    }
    else {
      $vv_logincount_per_month = ( $unique_logins ? $this->getUniqueLoginCountByRanges() : $this->utils->getLoginCountByRanges($conn)) ?: array();
      $vv_logins_per_country = array();
    }
    $vv_logincounts['datatable_dashboard'] = $vv_logincount_per_month;
    $vv_logincounts['map'] = $vv_logins_per_country;
    $this->response->type('json');
    $this->response->statusCode(201);
    $this->response->body(json_encode($vv_logincounts));
    return $this->response;
  }

  /**
   * getdataforuserstiles
   *
   * @return CakeResponse
   */

  public function getdataforuserstiles()
  {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    // We accept only Ajax Requests
    if(!$this->request->is('Ajax')) {
      return;
    }
    $this->autoRender = false; // We don't render a view
    $this->layout = null;

    $data = array();
    //last year
    $data[] = $this->CoPerson->find('count', array(
      'conditions' => array(
        'CoPerson.deleted' => false,
        'CoPerson.co_id' => $this->request->params['named']['co'],
        'CoPerson.status' => StatusEnum::Active,
      ),
      'contain' => false,
    ));
    //last 7 days 
    $data[] = $this->CoPerson->find('count', array(
      'conditions' => array(
        'CoPerson.deleted' => false,
        'CoPerson.co_id' => intVal($this->request->params['named']['co']),
        'CoPerson.status' => StatusEnum::Active,
        'CoPerson.created > CURRENT_DATE - INTERVAL \'7 days\'',
      ),
      'contain' => false,
    ));
    //last 30 days
    $data[] = $this->CoPerson->find('count', array(
      'conditions' => array(
        'CoPerson.deleted' => false,
        'CoPerson.co_id' => intVal($this->request->params['named']['co']),
        'CoPerson.status' => StatusEnum::Active,
        'CoPerson.created > CURRENT_DATE - INTERVAL \'30 days\'',
      ),
      'contain' => false,
    ));
    //last year
    $data[] = $this->CoPerson->find('count', array(
      'conditions' => array(
        'CoPerson.co_person_id' => NULL,
        'CoPerson.deleted' => false,
        'CoPerson.co_id' => intVal($this->request->params['named']['co']),
        'CoPerson.status' => StatusEnum::Active,
        'CoPerson.created > CURRENT_DATE - INTERVAL \'1 year\'',
      ),
      'contain' => false,
    ));
    $this->response->type('json');
    $this->response->statusCode(201);
    $this->response->body(json_encode($data));
    return $this->response;
  }
    
  /**
   * getstatspercou
   *
   * @return CakeResponse
   */

  public function getstatspercou()
  {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    // We accept only Ajax Requests
    if(!$this->request->is('Ajax')) {
      return;
    }
    $this->autoRender = false; // We don't render a view
    $this->layout = null;
    $co_id = $this->request->params['named']['co'];
    $cou_id = $this->request->query['cou_id'];

    $args = array(); 
    $args['joins'][0]['table'] = 'cous';
    $args['joins'][0]['alias'] = 'Cou';
    $args['joins'][0]['type'] = 'INNER';
    $args['joins'][0]['conditions'][0] = 'CoPersonRole.cou_id = Cou.id';
    $args['fields'] = array('count(*)', 'CoPersonRole.status');
    $args['conditions']['Cou.id'] = $cou_id;
    $args['conditions']['Cou.deleted'] = false;
    $args['conditions']['CoPersonRole.deleted'] = false;
    $args['conditions']['CoPersonRole.co_person_role_id'] = null;
    $args['group'] = array('CoPersonRole.status');
    $args['contain'] = false;
    $data['cou'] = $this->CoPersonRole->find('all', $args);
    if(!empty($this->utils->getStatisticsUserCountryTableName())) {
      $dataForMap = $this->findRegisteredUserCountryPerCommunity($this->utils->getStatisticsUserCountryTableName(), $co_id, $cou_id);
      $data['map'] = Hash::combine($dataForMap, '{n}.{n}.status', '{n}.{n}', '{n}.{n}.country');
    }
    else {
      $data['map'] = array();
    }
    $this->response->type('json');
    $this->response->statusCode(201);
    $this->response->body(json_encode($data));
    return $this->response;
  }
    
  /**
   * getuserscousowner
   *
   * @return CakeResponse
   */

  public function getuserscousowner()
  {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    // We accept only Ajax Requests
    if(!$this->request->is('Ajax')) {
      return;
    }
    $this->autoRender = false; // We don't render a view
    $this->layout = null;
    $co_id = $this->request->params['named']['co'];
    $this->cur_co['Co']['id'] = $co_id;
    $roles = $this->Role->calculateCMRoles();
    
    // Have we configured a privileged Group
    $roles['privileged'] = false;
    $cfg = $this->RciamStatsViewer->getConfiguration($this->cur_co['Co']['id']);
    if(!empty($cfg['RciamStatsViewer']['privileged_co_group_id'])) {
      // Find if my user is a member in this group
      $args = array();
      $args['conditions']['CoGroupMember.co_group_id'] = $cfg['RciamStatsViewer']['privileged_co_group_id'];
      $args['conditions']['CoGroupMember.co_person_id'] = $this->Session->read('Auth.User.co_person_id');
      $args['contain'] = false;
      $co_person_membership = $this->Co->CoGroup->CoGroupMember->find('all', $args);
      if(!empty($co_person_membership)) {
        $roles['privileged'] = true;
      }
    }   

    $args = array();
    // Check if user can see all cous or only theirs that is admin
    if($roles['coadmin'] === false && $roles['cmadmin'] === false && $roles['privileged'] === false){
      $curlRoles = $this->CoGroup->CoGroupMember->findCoPersonGroupRoles($roles['copersonid']);
      $args['conditions']['CoGroup.id'] = $curlRoles["owner"];
    }
    
    $args['joins'][0]['table'] = 'co_groups';
    $args['joins'][0]['alias'] = 'CoGroup';
    $args['joins'][0]['type'] = 'INNER';
    $args['joins'][0]['conditions'][0] = 'Cou.id = CoGroup.cou_id';
    $args['conditions']['Cou.cou_id'] = null;
    $args['conditions']['Cou.parent_id'] = null;
    $args['conditions']['CoGroup.co_id'] = $co_id;
    $args['conditions']['CoGroup.deleted'] = false;
    $args['fields'] = array("CoGroup.cou_id", "Cou.name","Cou.description", "Cou.created");
    $args['group'] = array("CoGroup.cou_id", "Cou.name","Cou.description", "Cou.created");
    $args['contain'] = false;

    $own = $this->Cou->find('all', $args);

    $this->response->type('json');
    $this->response->statusCode(201);
    $this->response->body(json_encode($own));
    return $this->response;
  }

  /**
   * getdataforcolumnschart
   * Is used at cou and registered users tab
   * 
   * @return CakeResponse
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
    if($tab === null || $tab === 'registered') {
      $status = 'A';
      if($tab === 'registered') {
        // find users that we want to find their countries (also first time initialize of tab)
        if(!empty($this->utils->getStatisticsUserCountryTableName()) && RciamStatsViewerDateTruncEnum::type[$range]  == RciamStatsViewerDateTruncEnum::yearly) {
          $data = $this->findRegisteredUsersAndCountries(RciamStatsViewerDateTruncEnum::type[$range], $this->utils->getStatisticsUserCountryTableName(), $co_id, $status, NULL);
          //$sql = "select count(*), date_trunc( 'year', created ) as range_date, min(created) as min_date from cm_co_people where co_person_id IS NULL AND NOT DELETED AND co_id=$co_id AND status='$status' AND created >
          //  date_trunc('year', CURRENT_DATE) - INTERVAL '1 year' group by date_trunc( 'year', created ) ORDER BY date_trunc( 'year', created ) ASC";
          //$data['data'] = $this->RciamStatsViewer->query($sql);
        }
        else {
          if(RciamStatsViewerDateTruncEnum::type[$range] === RciamStatsViewerDateTruncEnum::monthly) {
            $sql = "select count(*), date_trunc( 'month', created ) as range_date, min(created) as min_date from cm_co_people where co_person_id IS NULL AND NOT DELETED AND co_id=$co_id AND status='$status' AND created >
            date_trunc('month', CURRENT_DATE) - INTERVAL '1 year' group by date_trunc( 'month', created ) ORDER BY date_trunc( 'month', created ) ASC";
          } 
          else if(RciamStatsViewerDateTruncEnum::type[$range]  == RciamStatsViewerDateTruncEnum::yearly) {
            $sql = "select count(*), date_trunc( 'year', created ) as range_date, min(created) as min_date from cm_co_people where co_person_id IS NULL AND NOT DELETED AND co_id=$co_id AND status='$status' group by date_trunc( 'year', created ) ORDER BY date_trunc( 'year', created ) ASC";
          }
          else if(RciamStatsViewerDateTruncEnum::type[$range] == RciamStatsViewerDateTruncEnum::weekly) {
            $sql = "select count(*), date_trunc( 'week', created ) as range_date, min(created) as min_date from cm_co_people where co_person_id IS NULL AND NOT DELETED AND co_id=$co_id AND status='$status' AND created >
            date_trunc('month', CURRENT_DATE) - INTERVAL '6 months' group by date_trunc( 'week', created ) ORDER BY date_trunc( 'week', created ) ASC";
          }
          $data['data'] = $this->RciamStatsViewer->query($sql);
        }
      }
    } 
    else {
      $table = 'cm_cous';
      $tableColumn = 'cou_id';
      $status = '';
      $selectExtra = ", string_agg(name,'|| ') as names, string_agg(to_char(created, 'YYYY-MM-DD'),', ') as created_date, string_agg(description,'|| ') as description";
      $whereExtra = " AND parent_id IS NULL ";
      if(RciamStatsViewerDateTruncEnum::type[$range] === RciamStatsViewerDateTruncEnum::monthly) {
        $sql = "select count(*), date_trunc( 'month', created ) as range_date, min(created) as min_date $selectExtra from $table where $tableColumn IS NULL AND NOT DELETED AND co_id=$co_id $status $whereExtra AND created >
        date_trunc('month', CURRENT_DATE) - INTERVAL '1 year' group by date_trunc( 'month', created ) ORDER BY date_trunc( 'month', created ) ASC";
      } 
      else if(RciamStatsViewerDateTruncEnum::type[$range] === null || RciamStatsViewerDateTruncEnum::type[$range]  == RciamStatsViewerDateTruncEnum::yearly) {
        $sql = "select count(*), date_trunc( 'year', created ) as range_date, min(created) as min_date $selectExtra from $table where $tableColumn IS NULL AND NOT DELETED AND co_id=$co_id $status $whereExtra group by date_trunc( 'year', created ) ORDER BY date_trunc( 'year', created ) ASC";
      }
      else if(RciamStatsViewerDateTruncEnum::type[$range] == RciamStatsViewerDateTruncEnum::weekly) {
        $sql = "select count(*), date_trunc( 'week', created ) as range_date, min(created) as min_date $selectExtra from $table where $tableColumn IS NULL AND NOT DELETED AND co_id=$co_id $status $whereExtra AND created >
        date_trunc('month', CURRENT_DATE) - INTERVAL '6 months' group by date_trunc( 'week', created ) ORDER BY date_trunc( 'week', created ) ASC";
      }
      $data['data'] = $this->RciamStatsViewer->query($sql);
    }

    $this->response->type('json');
    $this->response->statusCode(201);
    $this->response->body(json_encode($data));
    return $this->response;
  }


  /**
   * getdatafordatatable
   *
   * @return CakeResponse
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
    $unique_logins = isset($this->request->query['unique_logins']) && $this->request->query['unique_logins'] == "true" ? true : false;

    $data = array();
    if($dateFrom != null && $dateTo != null && $dateTo >= $dateFrom) {

      if($type === null || $type === 'registered' || $type === 'cou') {
        $trunc_by = RciamStatsViewerDateTruncEnum::type[$groupBy] !== null ? RciamStatsViewerDateTruncEnum::type[$groupBy] : $trunc_by = RciamStatsViewerDateTruncEnum::monthly;
        
        if($type == null || $type == 'registered') {
          $table_name = $this->utils->getStatisticsUserCountryTableName();
          $status = 'A';
          $data = $this->findRegisteredUsersAndCountries($trunc_by, $table_name, $co_id, $status, array($dateFrom, $dateTo));
        } else {
          $table = 'cm_cous';
          $tableColumn = 'cou_id';
          $status = '';
          $selectExtra = ", string_agg(name,'|| ') as names, string_agg(to_char(created, 'YYYY-MM-DD'),', ') as created_date, string_agg(description,'|| ') as description";
          $whereExtra = ' AND parent_id IS NULL ';
          $sql = "select count(*), date_trunc('" . $trunc_by . "', created) as range_date, date_trunc('" . $trunc_by . "', created) as show_date, min(created) as min_date $selectExtra from $table where $tableColumn IS NULL AND NOT DELETED AND co_id=" . $co_id . " $status $whereExtra AND created BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "' group by date_trunc('" . $trunc_by . "',created)";
          $data['data'] = $this->RciamStatsViewer->query($sql);
        }
            
      } else {
        $fail = false;
        try {
          // Try to connect to the database
          $conn = $this->RciamStatsViewer->connect($co_id);
          switch($type) {
            case 'idp':
              $data["idps"] = $unique_logins ? $this->getUniqueAccessCountForServicePerIdentityProviders($conn, 0, $identifier, $dateFrom, $dateTo) : $this->utils->getLoginCountPerIdp($conn, 0, $identifier, $dateFrom, $dateTo);
              break;
            case 'sp':
              $data["sps"] = $unique_logins ? $this->getUniqueAccessCountForIdentityProviderPerServiceProviders($conn, 0, $identifier, $dateFrom, $dateTo) : $this->utils->getLoginCountPerSp($conn, 0, $identifier, $dateFrom, $dateTo);
              break;
            case 'dashboard':
              $data = $this->findCountriesFromLogins($conn, $groupBy, array($dateFrom, $dateTo), $unique_logins);
              break;
            case 'spSpecific':
              $data["idps"] = $unique_logins ? $this->getUniqueAccessCountForServicePerIdentityProviders($conn, 0, $identifier, $dateFrom, $dateTo) : $this->utils->getLoginCountPerIdp($conn, 0, $identifier, $dateFrom, $dateTo);
              $data["map"] = $this->findLoginsPerCountry($identifier, NULL, array($dateFrom, $dateTo), NULL, $unique_logins);
              break;
            case 'idpSpecific':
              $data["sps"] = $unique_logins ? $this->getUniqueAccessCountForIdentityProviderPerServiceProviders($conn, 0, $identifier, $dateFrom, $dateTo) : $this->utils->getLoginCountPerSp($conn, 0, $identifier, $dateFrom, $dateTo);
              $data["map"] = $this->findLoginsPerCountry(NULL, $identifier, array($dateFrom, $dateTo), NULL, $unique_logins);
              break;
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
          if($fail) {
            // Initialize frontend placeholders
            $data["sps"] = [];
            $data["idps"] = [];
            $data["maps"] = [];
            $data["dashboard"] = [];
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
      $unique_logins = isset($this->request->query['unique_logins']) && $this->request->query['unique_logins'] == "true" ? true : false;

      $dateTo = date("Y-m-d");
      if($days === 365) {
        $dateFrom = date('Y-m-d', strtotime('-364 days'));
        $groupBy = RciamStatsViewerDateEnum::monthly;
      } else if($days === 30) {
        $dateFrom = date('Y-m-d', strtotime('-29 days'));
        $groupBy = RciamStatsViewerDateEnum::daily;
      } else if($days === 7) {
        $dateFrom = date('Y-m-d', strtotime('-6 days'));
        $groupBy = RciamStatsViewerDateEnum::daily;
      } else if($days === 1) {
        $dateFrom = date('Y-m-d', strtotime('-0 days'));
        $groupBy = RciamStatsViewerDateEnum::daily;
      }
      else {
        $groupBy = NULL;
        $dateFrom = NULL;
        $dateTo = NULL;
      }

      if($type === null) { //Dashboard Summary
        $vv_logincount_per_day['range'] = $unique_logins ? $this->getUniqueLoginCountPerDayForProvider($days) : $this->utils->getLoginCountPerDayForProvider($conn, $days);
        $vv_logincount_per_day['idps'] = $unique_logins ? $this->getUniqueAccessCountForServicePerIdentityProviders($conn, $days) : $this->utils->getLoginCountPerIdp($conn, $days);
        $vv_logincount_per_day['sps'] = $unique_logins ? $this->getUniqueAccessCountForIdentityProviderPerServiceProviders($conn, $days) : $this->utils->getLoginCountPerSp($conn, $days);
        $data = $this->findCountriesFromLogins($conn, $groupBy, $dateFrom != NULL && $dateTo != NULL ? array($dateFrom, $dateTo) : NULL, $unique_logins);
        $vv_logincount_per_day['datatable'] = $data['dashboard'];
        $vv_logincount_per_day['map'] = $data['map'];
      } else if($type === "idp") {
        $vv_logincount_per_day['range'] = $unique_logins ? $this->getUniqueLoginCountPerDayForProvider($days, $identifier, $type) : $this->utils->getLoginCountPerDayForProvider($conn, $days, $identifier, $type);
        $vv_logincount_per_day['sps'] = $unique_logins ? $this->getUniqueAccessCountForIdentityProviderPerServiceProviders($conn, $days, $identifier) : $this->utils->getLoginCountPerSp($conn, $days, $identifier);
        $vv_logincount_per_day['map'] = $this->findLoginsPerCountry(NULL, $identifier, $dateFrom != NULL && $dateTo != NULL ? array($dateFrom, $dateTo) : NULL, NULL, $unique_logins);
      } else if($type === "sp") {
        $vv_logincount_per_day['range'] = $unique_logins ? $this->getUniqueLoginCountPerDayForProvider($days, $identifier, $type) : $this->utils->getLoginCountPerDayForProvider($conn, $days, $identifier, $type);
        $vv_logincount_per_day['idps'] = $unique_logins ? $this->getUniqueAccessCountForServicePerIdentityProviders($conn, $days, $identifier) : $this->utils->getLoginCountPerIdp($conn, $days, $identifier);
        $vv_logincount_per_day['map'] = $this->findLoginsPerCountry($identifier, NULL, $dateFrom != NULL && $dateTo != NULL ? array($dateFrom, $dateTo) : NULL, NULL, $unique_logins);
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
      if($fail) {
        // Initialize frontend placeholders
        $vv_logincount_per_day['range'] = array();
        $vv_logincount_per_day['idps'] = array();
        $vv_logincount_per_day['sps'] = array();
        $vv_logincount_per_day['map'] = array();
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
    $unique_logins = isset($this->request->query['unique_logins']) && $this->request->query['unique_logins'] == "true" ? true : false;
    $days = (isset($this->request->query['days']) ? $this->request->query['days'] : 0);
    $this->autoRender = false; // We don't render a view 
    $this->layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
    $conn = $this->RciamStatsViewer->connect($this->request->params['named']['co']);

    $vv_totalloginscount = ($unique_logins) ?
      array(
        $this->getTotalUniqueLoginCounts($conn, 1, $sp),
        $this->getTotalUniqueLoginCounts($conn, 7, $sp),
        $this->getTotalUniqueLoginCounts($conn, 30, $sp),
        $this->getTotalUniqueLoginCounts($conn, 365, $sp)
      ) : array(
        $this->utils->getTotalLoginCounts($conn, 1, $sp),
        $this->utils->getTotalLoginCounts($conn, 7, $sp),
        $this->utils->getTotalLoginCounts($conn, 30, $sp),
        $this->utils->getTotalLoginCounts($conn, 365, $sp)
      );
    $vv_logincounts['tiles'] = $vv_totalloginscount;
    $vv_logincounts['idp'] = $unique_logins ? $this->getUniqueAccessCountForServicePerIdentityProviders($conn, $days, $sp) : $this->utils->getAccessCountPerIdpOrSp($conn, $days, $sp, 'sp');
    $vv_logincounts['sp'] = $unique_logins ? $this->getUniqueLoginCountPerDayForProvider($days, $sp, "sp") : $this->utils->getLoginCountPerDayForProvider($conn, $days, $sp, "sp");
    $vv_logincounts['map'] = $this->findLoginsPerCountry($sp, NULL, NULL, NULL, $unique_logins);
    
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
    $this->autoRender = false; // We don't render a view
    $this->layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
    $idp = $this->request->query['idp'];
    $unique_logins = isset($this->request->query['unique_logins']) && $this->request->query['unique_logins'] == "true" ? true : false;
    $days = (isset($this->request->query['days']) ? $this->request->query['days'] : 0);
    $conn = $this->RciamStatsViewer->connect($this->request->params['named']['co']);
    $vv_totalloginscount = ($unique_logins) ?
      array(
        $this->getTotalUniqueLoginCounts($conn, 1, null, $idp),
        $this->getTotalUniqueLoginCounts($conn, 7, null, $idp),
        $this->getTotalUniqueLoginCounts($conn, 30, null, $idp),
        $this->getTotalUniqueLoginCounts($conn, 365, null, $idp)
      ) : array(
        $this->utils->getTotalLoginCounts($conn, 1, null, $idp),
        $this->utils->getTotalLoginCounts($conn, 7, null, $idp),
        $this->utils->getTotalLoginCounts($conn, 30, null, $idp),
        $this->utils->getTotalLoginCounts($conn, 365, null, $idp)
      );

    $vv_logincounts['sp'] = $unique_logins ? $this->getUniqueAccessCountForIdentityProviderPerServiceProviders($conn, $days, $idp) : $this->utils->getAccessCountPerIdpOrSp($conn, $days, $idp, 'idp');
    $vv_logincounts['idp'] = $unique_logins ? $this->getUniqueLoginCountPerDayForProvider($days, $idp, "idp") : $this->utils->getLoginCountPerDayForProvider($conn, $days, $idp, "idp");
    $vv_logincounts['tiles'] = $vv_totalloginscount;
    $vv_logincounts['map'] = $unique_logins ? $this->findLoginsPerCountry(NULL, $idp, NULL, NULL, $unique_logins) : $this->findLoginsPerCountry(NULL, $idp);

    $this->response->type('json');
    $this->response->statusCode(200);
    $this->response->body(json_encode($vv_logincounts));
    return $this->response;
  } 

  /**
   * getUniqueLoginCountPerDayForProvider
   * 
   * @param $days
   * @param $idpIdentifier
   * @param $type
   * @return mixed
   */

  public function getUniqueLoginCountPerDayForProvider($days, $identifier = NULL, $providerType = NULL)
  {
    $table_name = !empty($this->utils->getStatisticsCountryTableName()) ? $this->utils->getStatisticsCountryTableName() : array();
    if(empty($table_name))
      return array();
    if($providerType == 'idp') {
      $column = "sourceidp = '" . $identifier . "'";
    } else if ($providerType == 'sp') {
      $column = "service = '" . $identifier . "'";
    } else if ($providerType == null) {
      $column = "service != ''";
    }
    if($days === 0) {    // 0 = all time
      $sql = "SELECT date_part('year',date) as year, date_part('month',date) as  month, date_part('day',date) as day, count(DISTINCT hasheduserid) AS count FROM $table_name WHERE $column GROUP BY year, month,day ORDER BY year DESC,month DESC,day DESC";
    } else {
      $sql = "SELECT date_part('year',date) as year, date_part('month',date) as  month, date_part('day',date) as day, count(DISTINCT hasheduserid) AS count FROM $table_name WHERE $column AND date > current_date - INTERVAL '1 days' * $days GROUP BY year, month, day ORDER BY year DESC,month DESC,day DESC";
    }
    return $this->RciamStatsViewer->query($sql);
  }

  /**
   * getTotalUniqueLoginCounts
   * 
   * @param $conn
   * @param $days
   * @param null $sp
   * @param null $idp
   * @return int
   */

  public function getTotalUniqueLoginCounts($conn, $days, $sp = NULL, $idp = NULL)
  {
    assert($conn !== NULL);
    $table_name = !empty($this->utils->getStatisticsCountryTableName()) ? $this->utils->getStatisticsCountryTableName() : array();
    if(empty($table_name))
      return array();

    if($days === 0) {// 0 = all time
      if($sp === null && $idp === null) {
        $sql = "SELECT count(DISTINCT hasheduserid) AS count FROM $table_name WHERE service != ''";
      } else if($sp !== null) {
        $sql = "SELECT count(DISTINCT hasheduserid) AS count FROM $table_name WHERE service = '" . $sp . "'";
      } else if($idp !== null) {
        $sql = "SELECT count(DISTINCT hasheduserid) AS count FROM $table_name WHERE sourceidp = '" . $idp . "'";
      }
    } else {
      if($sp === null && $idp === null) {
        $sql = "SELECT count(DISTINCT hasheduserid) AS count FROM $table_name WHERE service != '' AND date::date > current_date - INTERVAL '1 days' * $days";
      } else if($sp !== null) {
        $sql = "SELECT count(DISTINCT hasheduserid) AS count FROM $table_name WHERE service = '" . $sp . "' AND date::date > current_date - INTERVAL '1 days' * $days";
      } else if($idp !== null) {
        $sql = "SELECT count(DISTINCT hasheduserid) AS count FROM $table_name WHERE sourceidp = '" . $idp . "' AND date::date > current_date - INTERVAL '1 days' * $days";
      }
    }
    $result = $this->RciamStatsViewer->query($sql);
    return !empty($result[0][0]['count']) ? $result[0][0]['count'] : 0;
  }

  /**
   * getUniqueAccessCountForServicePerIdentityProviders
   * 
   * @param $conn
   * @param $days
   * @param $spIdentifier
   * @return mixed
   */

  public function getUniqueAccessCountForServicePerIdentityProviders($conn, $days, $spIdentifier = NULL, $dateFrom = NULL, $dateTo = NULL)
  {
    $table_name = !empty($this->utils->getStatisticsCountryTableName()) ? $this->utils->getStatisticsCountryTableName() : array();
    if(empty($table_name))
      return array();
    $serviceSql = '';
    $service = '';
    if(!empty($spIdentifier)) {
      $serviceSql = "AND service = '".$spIdentifier."'";
      $service = ", service";
    }
    $subQuery = '';
    if($dateFrom != null && $dateTo != null && $dateTo > $dateFrom){ //ranges in datatable
        $subQuery = " WHERE date BETWEEN '". $dateFrom ."' AND '". $dateTo ."'";          
    }
    if($days === 0) {// 0 = all time
      $query = "SELECT sourceidp, count(DISTINCT hasheduserid) AS count FROM $table_name $subQuery GROUP BY sourceidp $service  HAVING sourceidp != '' $serviceSql ORDER BY count DESC";
    } else {
      $query = "SELECT sourceidp, count(DISTINCT hasheduserid) AS count FROM $table_name WHERE date > current_date - INTERVAL '1 days' * $days GROUP BY sourceidp $service HAVING sourceIdp != '' $serviceSql ORDER BY count DESC";
    }
    $result =  $this->RciamStatsViewer->query($query);
    return $this->utils->getInformationForProvider($conn, 'idp', $result);
  }

  /**
   * getUniqueAccessCountForIdentityProviderPerServiceProviders
   * 
   * @param $conn
   * @param $days
   * @param $spIdentifier
   * @return mixed
   */

  public function getUniqueAccessCountForIdentityProviderPerServiceProviders($conn, $days, $idpIdentifier = NULL, $dateFrom = NULL, $dateTo = NULL)
  {
    $table_name = !empty($this->utils->getStatisticsCountryTableName()) ? $this->utils->getStatisticsCountryTableName() : array();
    if(empty($table_name))
      return array();
    $idpSql = '';
    $idp = '';
    if(!empty($idpIdentifier)) {
      $idpSql = "AND sourceidp = '".$idpIdentifier."'";
      $idp = ", sourceidp";
    }
    $subQuery = '';
    if($dateFrom != null && $dateTo != null && $dateTo > $dateFrom) { //ranges in datatable
      $subQuery = " WHERE date BETWEEN '". $dateFrom ."' AND '". $dateTo ."'"; 
    }
    if($days === 0) {// 0 = all time
      $query = "SELECT service, count(DISTINCT hasheduserid) AS count FROM $table_name $subQuery GROUP BY service $idp HAVING service != '' $idpSql ORDER BY count DESC";
    } else { 
      $query = "SELECT service, count(DISTINCT hasheduserid) AS count FROM $table_name WHERE date > current_date - INTERVAL '1 days' * $days GROUP BY service $idp HAVING service != '' $idpSql ORDER BY count DESC";
    }
    $result =  $this->RciamStatsViewer->query($query);
    return $this->utils->getInformationForProvider($conn, 'sp', $result);
  }

  /**
   * getUniqueLoginCountByRanges
   *
   * @param  mixed $dateFrom
   * @param  mixed $dateTo
   * @param  mixed $groupBy
   * @return void
   */

  public function getUniqueLoginCountByRanges($dateFrom = NULL, $dateTo = NULL, $groupBy = NULL)
  {
    $table_name = !empty($this->utils->getStatisticsCountryTableName()) ? $this->utils->getStatisticsCountryTableName() : array();
    if(empty($table_name))
      return array();

    if(!empty($groupBy) && !empty(RciamStatsViewerDateTruncEnum::type[$groupBy])) {
      $trunc_by = RciamStatsViewerDateTruncEnum::type[$groupBy];
      $sql = "select count(DISTINCT hasheduserid) as count, date_trunc('" . $trunc_by . "', date) as range_date, date_trunc('" . $trunc_by . "', date) as show_date, min(date) as min_date from $table_name where service != '' AND date BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "' group by date_trunc('" . $trunc_by . "', date) ORDER BY range_date ASC";
    } else {            
      $trunc_by = RciamStatsViewerDateTruncEnum::monthly;
      $sql = "select count(DISTINCT hasheduserid) as count, date_trunc('" . $trunc_by . "', date) as range_date, date_trunc('" . $trunc_by . "', date) as show_date, min(date) as min_date from $table_name where service != ''  group by date_trunc('" . $trunc_by . "',date) ORDER BY range_date ASC";
    }

    return $this->RciamStatsViewer->query($sql);
  }

  /**
   * findLoginsPerCountry
   *
   * @param  mixed $sp
   * @param  mixed $idp
   * @param  mixed $dateFromTo
   * @param  mixed $groupBy
   * @return array
   */

  function findLoginsPerCountry($sp = NULL, $idp = NULL, $dateFromTo = NULL, $groupBy = NULL, $uniqueLogins = NULL)
  {
    $filterCountry = " AND country != 'Unknown' ";  
    $table_name = !empty($this->utils->getStatisticsCountryTableName()) ? $this->utils->getStatisticsCountryTableName() : array();
    if(empty($table_name))
      return array();

    $count = !empty($uniqueLogins) && $uniqueLogins ?  'count(DISTINCT hasheduserid) as sum' : 'SUM(count)';
    switch ($groupBy) {
      case NULL:
        $groupBySql = '';
        $groupByAs = '';
        break;
      default:
        $groupBySql = ", date_trunc('" . $groupBy . "', date)";
        $groupByAs = ", date_trunc('" . $groupBy . "', date) as " . $groupBy;
        break;
    }

    switch ($dateFromTo) {
      case NULL:
        if ($sp == NULL && $idp == NULL) {
          $sql = "SELECT $count, country, countrycode, min(date), max(date) $groupByAs FROM $table_name WHERE country != 'Unknown' GROUP BY country, countrycode $groupBySql";
        } else if ($sp != NULL) {
          $sql = "SELECT $count, country, countrycode, min(date), max(date) $groupByAs FROM $table_name WHERE service='" . $sp . "' $filterCountry GROUP BY country, countrycode $groupBySql";
        } else if ($idp != NULL) {
          $sql = "SELECT $count, country, countrycode, min(date), max(date) $groupByAs FROM $table_name WHERE sourceidp='" . $idp . "' $filterCountry GROUP BY country, countrycode $groupBySql";
        }
        break;
      default:
        if ($sp == NULL && $idp == NULL) {
          $sql = "SELECT $count, country, countrycode, min(date), max(date) $groupByAs FROM $table_name WHERE date BETWEEN '" . $dateFromTo[0] . "' AND '" . $dateFromTo[1] . "' $filterCountry GROUP BY country, countrycode $groupBySql";
        } else if ($sp != NULL) {
          $sql = "SELECT $count, country, countrycode, min(date), max(date) $groupByAs FROM $table_name WHERE service='" . $sp . "' AND date BETWEEN '" . $dateFromTo[0] . "' AND '" . $dateFromTo[1] . "' $filterCountry GROUP BY country, countrycode $groupBySql";
        } else if ($idp != NULL) {
          $sql = "SELECT $count, country, countrycode, min(date), max(date) $groupByAs FROM $table_name WHERE sourceidp='" . $idp . "' AND date BETWEEN '" . $dateFromTo[0] . "' AND '" . $dateFromTo[1] . "' $filterCountry GROUP BY country, countrycode $groupBySql";
        }
        break;
    }
    return $this->RciamStatsViewer->query($sql);
  }
  
  /**
   * findCountriesFromLogins
   *
   * @param  mixed  $conn
   * @param  string $group_by
   * @param  array  $dateFromTo
   * @return Object
   */

  function findCountriesFromLogins($conn, $group_by, $dateFromTo, $unique_logins = NULL) {
    $trunc_by = !empty($group_by) ? RciamStatsViewerDateTruncEnum::type[$group_by] : RciamStatsViewerDateTruncEnum::monthly;
     
    if(!empty($logins_per_country = $this->findLoginsPerCountry(NULL, NULL, $dateFromTo, $trunc_by, $unique_logins))) {
      // group countries logins by month
      $map_dashboard_group_by_date = Hash::combine($logins_per_country, '{n}.{n}.country', '{n}.{n}', '{n}.{n}.' . $trunc_by);
      // find sum country-based logins per date 
      foreach($map_dashboard_group_by_date as $key => $value) {
        $sum = 0;
        foreach($value as $key_name => $country) {
          $map_dashboard_group_by_date[$key][$key_name]['count'] = $country['sum'];
          $sum += $country['sum'];
        }
        $counts_per_date[$key]['count'] = $sum;
      }
      // get overall number of logins (this data will be different from country-based as country tables were added after)
      $logincounts_trunc_by = (!empty($unique_logins) ? $this->getUniqueLoginCountByRanges($dateFromTo[0], $dateFromTo[1], $group_by) : $this->utils->getLoginCountByRanges($conn, $dateFromTo[0], $dateFromTo[1], $group_by)) ?: array();

      // we have to format data properly, and count unknown country-based logins
      foreach($logincounts_trunc_by as $key => $row) {
        if(!empty($map_dashboard_group_by_date[$row[0]['range_date']])) {
          $logincounts_trunc_by[$key][0]['countries'] = $map_dashboard_group_by_date[$row[0]['range_date']];
          $unknown_logins = $logincounts_trunc_by[$key][0]['count'] - $counts_per_date[$row[0]['range_date']]['count'];
          if ($unknown_logins > 0) {
            $logincounts_trunc_by[$key][0]['countries']['Unknown'] = array('name' => 'Unknown', 'count' => $unknown_logins);
          }
        } 
        else {
          $logincounts_trunc_by[$key][0]['countries']['Unknown'] = array('name' => 'Unknown', 'count' => $logincounts_trunc_by[$key][0]['count']);
        }
      }
      $data['map'] = $this->findLoginsPerCountry(NULL, NULL, $dateFromTo, NULL, $unique_logins); 
      $data['dashboard'] = $logincounts_trunc_by;
    }
    else {
      $data['map'] = array();
      $data['dashboard'] =  (!empty($unique_logins) ? $this->getUniqueLoginCountByRanges($dateFromTo[0], $dateFromTo[1], $group_by) : $this->utils->getLoginCountByRanges($conn, $dateFromTo[0], $dateFromTo[1], $group_by)) ?: array();
    }
    return $data;
  }
  
  /**
   * findRegisteredUserCountryPerCommunity
   *
   * @param  string   $users_country
   * @param  integer  $co_id
   * @param  integer  $cou_id
   * @param  string   $status
   * @return array    $data
   */

  function findRegisteredUserCountryPerCommunity($users_country, $co_id, $cou_id) {

    $sql = "SELECT cm_co_person_roles.status, t.country,t.countrycode,count(t.country) as sum, min(min_date), max(max_date) ".
           "FROM (SELECT userid, country, countrycode, sum(count) as sum_count, min(date) as min_date, max(date) as max_date ".
                  "FROM " . $users_country . 
                  " WHERE country != 'Unknown' ". // dont take into account Unknown as country
                 " GROUP BY userid, country, countrycode) t ".
           "JOIN (SELECT userid, max(sum_count) as max_sum_count ".
                  "FROM (SELECT userid, country, countrycode, sum(count) as sum_count ".
                        "FROM " . $users_country . 
                        " WHERE userid IN (".
                            "SELECT cm_identifiers.identifier FROM cm_co_people JOIN cm_identifiers ON cm_identifiers.co_person_id = cm_co_people.id ".
                            "JOIN cm_co_person_roles ON cm_co_person_roles.co_person_id = cm_co_people.id ". 
                            "WHERE cou_id = $cou_id AND NOT cm_co_person_roles.deleted ".
                              "AND cm_co_person_roles.co_person_role_id IS NULL AND cm_co_people.co_person_id IS NULL ".
                              "AND NOT cm_co_people.DELETED AND co_id=$co_id " . //AND cm_co_people.status='".$status."'
                              "AND cm_identifiers.identifier_id IS NULL) ".
                          "AND country != 'Unknown' ". // dont take into account Unknown as country
                        "GROUP BY userid, country, countrycode) x ".
                  "GROUP BY userid) y ".
           "ON t.userid=y.userid AND t.sum_count=y.max_sum_count ".
	         "JOIN cm_identifiers ON identifier = y.userid AND cm_identifiers.identifier_id IS NULL AND cm_identifiers.status='A' ANd NOT cm_identifiers.deleted ".
	         "JOIN cm_co_person_roles ON cm_co_person_roles.co_person_id = cm_identifiers.co_person_id AND NOT cm_co_person_roles.deleted AND cm_co_person_roles.co_person_role_id IS NULL AND cou_id = $cou_id ".
	         "GROUP BY t.country,t.countrycode, cm_co_person_roles.status";
   
     $data = $this->RciamStatsViewer->query($sql);

     return $data;
  }
  
  /**
   * findRegisteredUsersAndCountries
   *
   * @param  string   $truncBy
   * @param  string   $users_country
   * @param  integer  $co_id
   * @param  string   $status
   * @param  array    $dateFromTo
   * @return Object
   */

  function findRegisteredUsersAndCountries($truncBy, $users_country, $co_id, $status, $dateFromTo = NULL) {
    $status_sql = " AND cm_co_people.status='" . $status . "' ";
    $date_minus_2 = strtotime(date('Y-m-d H:i:s').' -2 year');
    $between = !empty($dateFromTo) ? " AND cm_co_people.created BETWEEN '" . $dateFromTo[0] . "' AND '" . $dateFromTo[1] . "'" 
      : " AND cm_co_people.created BETWEEN '".date('Y-m-d H:i:s', $date_minus_2)."' AND '".date('Y-m-d H:i:s')."'";
    // Map: get countries for registered users, created at a specific date range
    $sql = "SELECT t.country,t.countrycode,count(t.country) as sum, min(min_date), max(max_date) ".
           "FROM (SELECT userid, country, countrycode, sum(count) as sum_count, min(date) as min_date, max(date) as max_date ".
                 "FROM " . $users_country . " GROUP BY userid, country, countrycode) t ".
           "JOIN (SELECT userid, max(sum_count) as max_sum_count "."
                  FROM (SELECT userid, country, countrycode, sum(count) as sum_count FROM " . $users_country . 
                      " WHERE userid IN (".
                                        "SELECT cm_identifiers.identifier FROM cm_co_people JOIN cm_identifiers ON cm_identifiers.co_person_id = cm_co_people.id AND NOT cm_identifiers.deleted AND identifier_id IS NULL " .
                                        "where cm_co_people.co_person_id IS NULL AND NOT cm_co_people.DELETED AND co_id=$co_id $status_sql $between) ".
                              "AND country != 'Unknown' ". // dont take into account Unknown as country
                       "GROUP BY userid, country, countrycode) x ".
                  "GROUP BY userid) y ".
          "ON t.userid=y.userid AND t.sum_count=y.max_sum_count GROUP BY t.country,t.countrycode";
    
    $data['map'] = $this->RciamStatsViewer->query($sql);  

    // Datatable Information
    $sql = "SELECT country, date_trunc('" . $truncBy . "', cm_co_people.created) as range_date, " .
              "date_trunc('" . $truncBy . "', cm_co_people.created) as show_date, cm_co_people.created as registered_date " .
           "FROM cm_co_people " .
           "JOIN cm_identifiers " .
              "ON cm_identifiers.co_person_id = cm_co_people.id " .
              "AND NOT cm_identifiers.deleted AND identifier_id IS NULL " .
           "JOIN (". "
                  SELECT t.userid, t.country,t.countrycode,count(t.country) as sum, " .
                  "min(min_date), max(max_date) " .
                  "FROM (".
                          "SELECT userid, country, countrycode, sum(count) as sum_count, min(date) as min_date, " .
                            "max(date) as max_date " .
                          "FROM $users_country " .
                          "WHERE country != 'Unknown' ". // dont take into account Unknown as country
                          "GROUP BY userid, country, countrycode".
                        ") t " .
                   "JOIN (".
                          "SELECT userid, max(sum_count) as max_sum_count " .
                          "FROM (".
                                 "SELECT userid, country, countrycode, sum(count) as sum_count " .
                                 "FROM $users_country " .
                                 "WHERE country != 'Unknown' ". // dont take into account Unknown as country
                                 "GROUP BY userid, country, countrycode".
                                 ") x " .
                          "GROUP BY userid ".
                          ") y " .
                    "ON t.userid=y.userid AND t.sum_count=y.max_sum_count " .
                    "GROUP BY t.userid, t.country,t.countrycode".
                  ") users_country " .
      "ON users_country.userid = cm_identifiers.identifier " .
      "WHERE cm_co_people.co_person_id IS NULL AND NOT cm_co_people.DELETED AND co_id=$co_id $status_sql " .
      "$between ";
    // Find those that we dont have any information about their country.
    $sql .= " UNION SELECT NULL as country, date_trunc('" . $truncBy . "', cm_co_people.created) as range_date, " .
                "date_trunc('" . $truncBy . "', cm_co_people.created) as show_date, cm_co_people.created as registered_date ".
              "FROM cm_co_people " .
              "WHERE cm_co_people.id NOT IN (".
                  "SELECT cm_co_people.id ".
                  "FROM cm_co_people ".
                  "JOIN cm_identifiers ON cm_co_people.id = cm_identifiers.co_person_id  ".
                  "JOIN $users_country ON userid = identifier AND country != 'Unknown'".
                ") ".
              "AND cm_co_people.co_person_id IS NULL AND NOT cm_co_people.DELETED AND co_id=$co_id $status_sql $between ".
              "ORDER BY show_date ASC";
    $result = $this->RciamStatsViewer->query($sql);
    $created_date = $result[0][0]['range_date'];
    $i = 0;
    // initialize data 
    $data['data'] = array();
    foreach($result as $key=>$value) {
      // Change index if range_date changed
      if($created_date != $result[$key][0]['range_date']) {
        $i++;
        $created_date = $result[$key][0]['range_date'];
      }
      // When is initialised
      if(empty($data['data'][$i])) {
        $data['data'][$i][0]['count'] = 1;
        $data['data'][$i][0]['range_date'] = $result[$key][0]['range_date'];
        $data['data'][$i][0]['show_date'] = $result[$key][0]['show_date'];
        $data['data'][$i][0]['min_date'] = $result[$key][0]['registered_date'];
      }
      else {
        $data['data'][$i][0]['count'] ++;
      }
      
      $country_name = $result[$key][0]['country'] == NULL ? 'Unknown' : $result[$key][0]['country'];
      // Store user's country
      if(empty($data['data'][$i][0]['countries'][$country_name])) {
        $data['data'][$i][0]['countries'][$country_name]['count'] = 1;
        $data['data'][$i][0]['countries'][$country_name]['name'] = $country_name;
      }
      else {
        $data['data'][$i][0]['countries'][$country_name]['count'] ++;
      }
    }
    return $data;
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
    if($this->request->is('ajax')) {
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

    // Have we configured a privileged Group
    $roles['privileged'] = false;
    $cfg = $this->RciamStatsViewer->getConfiguration($this->cur_co['Co']['id']);
    if(!empty($cfg['RciamStatsViewer']['privileged_co_group_id'])) {
      // Find if my user is a member in this group
      $args = array();
      $args['conditions']['CoGroupMember.co_group_id'] = $cfg['RciamStatsViewer']['privileged_co_group_id'];
      $args['conditions']['CoGroupMember.co_person_id'] = $this->Session->read('Auth.User.co_person_id');
      $args['contain'] = false;
      $co_person_membership = $this->Co->CoGroup->CoGroupMember->find('all', $args);
      if(!empty($co_person_membership)) {
        $roles['privileged'] = true;
      }
    }

    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();

    // Determine what operations this user can perform
    $p['index'] = ($roles['comember'] || $roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin'] || $roles['privileged']);
    $p['getdataforsp'] = ($roles['comember'] || $roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin'] || $roles['privileged']);
    $p['getdataforidp'] = ($roles['comember'] || $roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin'] || $roles['privileged']);
    $p['getlogincountperday'] = ($roles['comember'] || $roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin'] || $roles['privileged']);
    $p['getdataforcolumnchart']  = ($roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin'] || $roles['privileged']);
    $p['getuserscousowner'] = ($roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin'] || $roles['privileged']);
    $p['general_cous_stats'] = ($roles['cmadmin'] || $roles['coadmin'] || $roles['privileged']);
    // Tab Permissions
    $p['idp'] = ($roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin'] || $roles['privileged']);
    $p['sp'] = ($roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin'] || $roles['privileged']);
    $p['registered'] = ($roles['cmadmin'] || $roles['coadmin'] || $roles['privileged']);
    $p['cou'] = ($roles['cmadmin'] || $roles['coadmin'] || $roles['privileged'] || $roles['couadmin']);
    $this->set('vv_permissions', $p);

    return ($p[$this->action]);
  }
}
