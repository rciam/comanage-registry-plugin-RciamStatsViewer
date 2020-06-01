<?php
/**
 * COmanage Registry Rciam Stats Viewer Plugin Language File
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.1.x
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
  
global $cm_lang, $cm_texts;

// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.

$cm_rciam_stats_viewer_texts['en_US'] = array(
  // Titles, per-controller
  'ct.rciam_stats_viewers.1'          => 'Statistics Viewer',
  'ct.rciam_stats_viewers.pl'         => 'Statistics Viewers',
  'ct.rciam_stats_viewer_services.pl' => 'Statistics Viewer',
  
  // Plugin texts
  'pl.rciamstatsviewer.hostname'        => 'Hostname',
  'pl.rciamstatsviewer.type'            => 'Type',
  'pl.rciamstatsviewer.database'        => 'Database',
  'pl.rciamstatsviewer.db_settings'     => 'Database Configuration',
  'pl.rciamstatsviewer.pl_config'       => 'Statistics Configuration',
  'pl.rciamstatsviewer.summary'         => 'Summary',
  'pl.rciamstatsviewer.idp.pl'          => 'Identity Providers',
  'pl.rciamstatsviewer.sp.pl'           => 'Service Providers',
  'pl.rciamstatsviewer.idp.tabname.pl'  => 'Identity Providers Details',
  'pl.rciamstatsviewer.sp.tabname.pl'   => 'Service Providers Details',
  'pl.rciamstatsviewer.registered.pl'   => 'Registered Users',
  'pl.rciamstatsviewer.cou.pl'          => 'Communities',

  'pl.rciamstatsviewer.registered.tabname.pl'                => 'Registered Users Details',
  'pl.rciamstatsviewer.cou.tabname.pl'                       => 'Communities Details',
  'pl.rciamstatsviewer.statisticsTableName'                  => 'Statistics Table Name',
  'pl.rciamstatsviewer.statisticsTableName.desc'             => 'Name of the statistics table at the database',
  'pl.rciamstatsviewer.serviceProvidersMapTableName'         => 'Service Providers Map Table Name',
  'pl.rciamstatsviewer.serviceProvidersMapTableName.desc'    => 'Name of the service providers table at the database',
  'pl.rciamstatsviewer.identityProvidersMapTableName'        => 'Identity Providers Map Table Name',
  'pl.rciamstatsviewer.identityProvidersMapTableName.desc'   => 'Name of the identity providers table at the database',

  'pl.rciamstatsviewer.dashboard.overall'       => 'Overall number of logins per day',
  'pl.rciamstatsviewer.dashboard.idp'           => 'Overall number of logins per IdP',
  'pl.rciamstatsviewer.dashboard.sp'            => 'Overall number of logins per SP',
  'pl.rciamstatsviewer.idp.overall'             => 'Overall number of logins from this IdP per day',
  'pl.rciamstatsviewer.sp.overall'              => 'Overall number of logins from this SP per day',
  'pl.rciamstatsviewer.idp.numberoflogins'      => 'Number of logins per Identity Provider',
  'pl.rciamstatsviewer.sp.numberoflogins'       => 'Number of logins per Service Provider',
  'pl.rciamstatsviewer.idp.numberoflogins.desc' => 'Click a specific identity provider to view detailed statistics.',
  'pl.rciamstatsviewer.sp.numberoflogins.desc'  => 'Click a specific service provider to view detailed statistics.',
  'pl.rciamstatsviewer.idp.specific'            => 'Service Providers that have been accessed by this Identity Provider',
  'pl.rciamstatsviewer.idp.specific.datatable'  => 'Service Providers',
  'pl.rciamstatsviewer.sp.specific'             => 'Identity Providers that have been accessed by this Service Provider',
  'pl.rciamstatsviewer.sp.specific.datatable'   => 'Identity Providers',
  'pl.rciamstatsviewer.registered.users.period' => 'Registered Users per Period',
  'pl.rciamstatsviewer.datatable.export'        => 'Export',
  'pl.rciamstatsviewer.registered.column'       => 'Number of Registered Users',
  'pl.rciamstatsviewer.registered.tooltip'      => 'Registered Users',
  'pl.rciamstatsviewer.registeredcolumn.chart'  => 'Number of Registered Users created per Period',
  'pl.rciamstatsviewer.coucolumn.chart'         => 'Number of Communities created per Period',
  'pl.rciamstatsviewer.cou.column'              => 'Number of Communities',
  'pl.rciamstatsviewer.cou.tooltip'             => 'Communities',
  'pl.rciamstatsviewer.dashboard.column'        => 'Number of Logins',
  'pl.rciamstatsviewer.dashboard.logins'        => 'Number of Logins',

  'pl.rciamstatsviewer.registered.defaultexporttitle' => 'Registered Users per Year',
  'pl.rciamstatsviewer.cou.defaultexporttitle'        => 'Communities Created per Year',

  'pl.rciamstatsviewer.registered.users.weekly'   => 'Week Number (Year)',
  'pl.rciamstatsviewer.registered.users.monthly'  => 'Year-Month',
  'pl.rciamstatsviewer.registered.users.yearly'   => 'Year',

  // Privileged User
  'pl.rciamstatsviewer.privileged.group'         => 'Privileged Group',
  'pl.rciamstatsviewer.privileged.group.desc'    => 'Users belonging in the group will have privileged access to Statistics Viewer',
  'pl.rciamstatsviewer.privileged.gr'            => 'Members of this Group are privileged users (CO admins are by default)',
  'pl.rciamstatsviewer.privileged.pl'            => 'Privileged',

  // TODO: We should remove this as soon as we upgrade to CM v3.3 or newer
  'fd.server'                          => 'Server',
  'fd.server.url'                      => 'Server URL',
  'fd.server.username'                 => 'Username',
  'fd.server.port'                     => 'Port',
  'fd.server.persistent'               => 'Persistent',
  'fd.server.encoding'                 => 'Encoding',
  'fd.server.test_connection'          => 'Test Connection',


  //Database
  'er.rciam_stats_viewer.db.save'    => 'Save failed',
  'er.rciam_stats_viewer.db.blackhauled'    => 'Token expired.Please try again.',
  'er.rciam_stats_viewer.db.connect' => 'Failed to connect to database: %1$s',
  'er.rciam_stats_viewer.db.action'  => 'Database action failed [PDO Code:%1$s]',
  'rs.rciam_stats_viewer.db.connect' => 'Database Connect Successful'
);
