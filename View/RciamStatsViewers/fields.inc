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
  
  if(($this->action == "add" && $permissions['add']) || ($this->action == "edit" && $permissions['edit']))
    $e = true;
    
  // We shouldn't get here if we don't have at least read permission, but check just in case
  
  if(!$e && !$permissions['view'])
    return false;
  
  // Add breadcrumbs
  print $this->element("coCrumb");

  $args = array();
  $args['plugin'] = null;
  $args['controller'] = 'rciam_stats_viewers';
  $args['action'] = 'index';
  $args['co'] = $cur_co['Co']['id'];
  $this->Html->addCrumb(_txt('ct.rciam_stats_viewers.pl'), $args);

  $args = array();
  $args['plugin'] = null;
  $args['controller'] = 'rciam_stats_viewers';
  $args['action'] = 'edit';
  $args[] = $cur_co['Co']['id'];//not sure
  $this->Html->addCrumb($rciam_stats_viewers[0]['Co']['description'], $args);

  $crumbTxt = _txt('op.config');
  $this->Html->addCrumb($crumbTxt);
  
  print $this->Form->hidden('co_id', array('default' => $cur_co['Co']['id'])) . "\n";
  
  $l = 1;
?>
<ul id="<?php print $this->action; ?>_rciam_stats_viewer" class="fields form-list">
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.rciamstatsviewer.host_name'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.rciamstatsviewer.host_name.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('host_name', array('size' => 50)) : filter_var($rciam_stats_viewers[0]['rciamstatsviewer']['host_name'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.rciamstatsviewer.stats_type'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.rciamstatsviewer.stats_type.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('stats_type', array('size' => 50)) : filter_var($rciam_stats_viewers[0]['rciamstatsviewer']['stats_type'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
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
