<!--
/**
 * COmanage Registry Rciam Stats Viewer Plugin Fields
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
 * @since         COmanage Registry v2.0.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
-->
<?php
  // Determine if fields are editable
  $e = false;
  
  if($this->action == "edit" && $permissions['edit'])
    $e = true;
    
  // We shouldn't get here if we don't have at least read permission, but check just in case
  if(!$e && !$permissions['view'])
    return false;
  
  // Add breadcrumbs
  print $this->element("coCrumb");

  $model = $this->name;
  $controller = Inflector::underscore(Inflector::pluralize($model));

  $crumbTxt = _txt('op.config')." "._txt('ct.rciam_stats_viewers.1');
  $this->Html->addCrumb($crumbTxt);
  
  print $this->Form->hidden('co_id', array('default' => $cur_co['Co']['id'])) . "\n";
?>
<ul id="configuration-menu" id="<?php print $this->action; ?>_rciam_stats_viewer">
  <li id="fields-database" class="fieldGroup">
    <a href="#tabs-database" class="fieldGroupName">
      <em class="material-icons">indeterminate_check_box</em>
      <?php 
        print 'Database Settings';//_txt('fd.name'); 
      ?>
    </a>
    <ul id="tabs-database" class="fields data-list form-list">
      <li>
        <div class="field-name">
          <div class="field-title">
            <?php print _txt('pl.rciamstatsviewer.hostname'); ?>
            <span class="required">*</span>
          </div>
          <div class="field-desc"><?php print _txt('pl.rciamstatsviewer.hostname.desc'); ?></div>
        </div>
        <div class="field-info">
          <?php 
          $value = empty($rciam_stats_viewers['RciamStatsViewer']['hostname']) ? ""
                  : filter_var($rciam_stats_viewers['RciamStatsViewer']['hostname'],FILTER_SANITIZE_SPECIAL_CHARS);
          print $this->Form->input('hostname', array('size' => 50,'value' => $value)); 
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <?php print $this->Form->label('port',_txt('fd.server.port')); ?>
        </div>
        <div class="field-info">
          <?php 
            $value = empty($rciam_stats_viewers['RciamStatsViewer']['port']) ? ""
            : filter_var($rciam_stats_viewers['RciamStatsViewer']['port'],FILTER_SANITIZE_SPECIAL_CHARS);
            print $this->Form->input('port', array('size' => 50,'value' => $value)); 
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <?php print ($e ? $this->Form->label('type', _txt('fd.type')) : _txt('fd.type')); ?><span class="required">*</span>
        </div>
        <div class="field-info">
          <?php
            global $cm_lang, $cm_texts;
            $attrs = array();
            $attrs['value'] = (isset($rciam_stats_viewers) ? $rciam_stats_viewers['RciamStatsViewer']['type'] : "");
            $attrs['empty'] = false;

            if($e) {
              print $this->Form->select('type',
                                        $vv_dbdriver_type_list,
                                        $attrs);

              if($this->Form->isFieldError('type')) {
                print $this->Form->error('type');
              }
            } else {
              print _txt('en.server.sql', null, $rciam_stats_viewers['RciamStatsViewer']['type']);
            }
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <?php print $this->Form->label('username',_txt('fd.server.username')); ?>
          <span class="required">*</span>
        </div>
        <div class="field-info">
          <?php 
            $value = empty($rciam_stats_viewers['RciamStatsViewer']['username']) ? ""
            : filter_var($rciam_stats_viewers['RciamStatsViewer']['username'],FILTER_SANITIZE_SPECIAL_CHARS);
            print $this->Form->input('username', array('size' => 50,'value' => $value)); 
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <?php print $this->Form->label('password',_txt('fd.password')); ?>
          <span class="required">*</span>
        </div>
        <div class="field-info">
          <?php 
            $value = empty($rciam_stats_viewers['RciamStatsViewer']['password']) ? ""
            : filter_var($rciam_stats_viewers['RciamStatsViewer']['password'],FILTER_SANITIZE_SPECIAL_CHARS);
            print $this->Form->input('password', array('size' => 50,'value' => $value));   
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <?php print $this->Form->label('databas',_txt('pl.rciamstatsviewer.database')); ?>
          <span class="required">*</span>
        </div>
        <div class="field-info">
          <?php 
            $value = empty($rciam_stats_viewers['RciamStatsViewer']['databas']) ? ""
            : filter_var($rciam_stats_viewers['RciamStatsViewer']['databas'],FILTER_SANITIZE_SPECIAL_CHARS);
            print $this->Form->input('databas', array('size' => 50,'value' => $value)); 
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <?php print $this->Form->label('db_prefix',_txt('pl.rciamstatsviewer.db_prefix')); ?>
        </div>
        <div class="field-info">
          <?php 
            $value = empty($rciam_stats_viewers['RciamStatsViewer']['db_prefix']) ? ""
            : filter_var($rciam_stats_viewers['RciamStatsViewer']['db_prefix'],FILTER_SANITIZE_SPECIAL_CHARS);
            print $this->Form->input('db_prefix', array('size' => 50,'value' => $value)); 
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <?php print $this->Form->label('persistent',_txt('pl.rciamstatsviewer.persistent')); ?>
          <span class="required">*</span>
        </div>
        <div class="field-info">
          <?php 
            $attrs = array();
            $attrs['value'] = (isset($rciam_stats_viewers) ? $rciam_stats_viewers['RciamStatsViewer']['persistent'] : "");
            $attrs['empty'] = false;

            if($e) {
              print $this->Form->select('persistent',
                                        $vv_persistent_list,
                                        $attrs);

              if($this->Form->isFieldError('persistent')) {
                print $this->Form->error('persistent');
              }
            } else {
              print _txt('en.server.sql', null, $rciam_stats_viewers['RciamStatsViewer']['persistent']);
            }
          
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <?php print $this->Form->label('encoding',_txt('pl.rciamstatsviewer.encoding')); ?>
          <span class="required">*</span>
        </div>
        <div class="field-info">
          <?php 
            $attrs = array();
            $attrs['value'] = (isset($rciam_stats_viewers) ? $rciam_stats_viewers['RciamStatsViewer']['encoding'] : "");
            $attrs['empty'] = false;

            if($e) {
              print $this->Form->select('encoding',
                                        $vv_encoding_list,
                                        $attrs);

              if($this->Form->isFieldError('encoding')) {
                print $this->Form->error('encoding');
              }
            } else {
              print _txt('en.server.sql', null, $rciam_stats_viewers['RciamStatsViewer']['encoding']);
            }
          
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <?php print $this->Form->label('statisticsTableName',_txt('pl.rciamstatsviewer.statisticsTableName')); ?>
          <span class="required">*</span>
        </div>
        <div class="field-info">
          <?php 
            $value = empty($rciam_stats_viewers['RciamStatsViewer']['statisticsTableName']) ? ""
            : filter_var($rciam_stats_viewers['RciamStatsViewer']['statisticsTableName'],FILTER_SANITIZE_SPECIAL_CHARS);
            print $this->Form->input('statisticsTableName', array('size' => 50,'value' => $value)); 
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <?php print $this->Form->label('identityProvidersMapTableName',_txt('pl.rciamstatsviewer.identityProvidersMapTableName')); ?>
          <span class="required">*</span>
        </div>
        <div class="field-info">
          <?php 
            $value = empty($rciam_stats_viewers['RciamStatsViewer']['identityProvidersMapTableName']) ? ""
            : filter_var($rciam_stats_viewers['RciamStatsViewer']['identityProvidersMapTableName'],FILTER_SANITIZE_SPECIAL_CHARS);
            print $this->Form->input('identityProvidersMapTableName', array('size' => 50,'value' => $value)); 
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <?php print $this->Form->label('serviceProvidersMapTableName',_txt('pl.rciamstatsviewer.databserviceProvidersMapTableNamease')); ?>
          <span class="required">*</span>
        </div>
        <div class="field-info">
          <?php 
            $value = empty($rciam_stats_viewers['RciamStatsViewer']['serviceProvidersMapTableName']) ? ""
            : filter_var($rciam_stats_viewers['RciamStatsViewer']['serviceProvidersMapTableName'],FILTER_SANITIZE_SPECIAL_CHARS);
            print $this->Form->input('serviceProvidersMapTableName', array('size' => 50,'value' => $value)); 
          ?>
        </div>
      </li>
    </ul>
  </li>
  <li id="fields-stats" class="fieldGroup">
    <a href="#tabs-stats" class="fieldGroupName">
          <em class="material-icons">indeterminate_check_box</em>
          <?php print _txt('fd.name'); ?>
    </a>
    <ul id="tabs-stats" class="fields data-list form-list">
      <li>
        <div class="field-name">
          <div class="field-title">
            <?php print _txt('pl.rciamstatsviewer.stats_type'); ?>
            <span class="required">*</span>
          </div>
          <div class="field-desc"><?php print _txt('pl.rciamstatsviewer.stats_type.desc'); ?></div>
        </div>
        <div class="field-info">
          <?php 
            $attrs=array();
            $attrs['value'] = (isset($rciam_stats_viewers['RciamStatsViewer']['stats_type'])
              ? $rciam_stats_viewers['RciamStatsViewer']['stats_type']
              : "");
            $attrs['empty'] = false;
            print $this->Form->select('stats_type',$vv_stats_type_list,$attrs); 
          ?>
        </div>
      </li>
    </ul>
  </li>
  <?php if($e): ?>
    <li class="fields-submit">
      <div class="field-name">
        <span class="required"><?php print _txt('fd.req'); ?></span>
      </div>
      <div class="field-info">
        <?php print $this->Form->submit($submit_label); ?>
      </div>
    </li>
  <?php endif; ?>
</ul>
<script type="text/javascript">
  <!-- /* JS specific to these fields */ -->
  $(function() {
    // Explorer menu toggles
    $(".fieldGroupName").click(function(event) {
      event.preventDefault();
      $(this).next(".fields").slideToggle("fast");
      // toggle the +/- icon:
      if ($(this).find(".material-icons").text() == "indeterminate_check_box") {
        $(this).find(".material-icons").text("add_box");
      } else {
        $(this).find(".material-icons").text("indeterminate_check_box");
      }
    });   
  });
</script>