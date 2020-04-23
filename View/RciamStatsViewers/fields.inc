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
  // Store the token
  $token_key = $this->request->params['_Token']['key'];

  // Load CSS and JS Libraries
  print $this->Html->css('/RciamStatsViewer/css/bootstrap.min');
?>

<div id="<?php print $this->action; ?>_rciam_stats" class="explorerContainer">
    <div id="rciamStats" class="personExplorer">
        <ul>
          <!-- RCIAM Stats Config -->
          <li id="fields-database" class="fieldGroup">
            <a href="#tabs-database" class="fieldGroupName">
              <em class="material-icons">indeterminate_check_box</em>
              <?php
                print _txt('pl.rciamstatsviewer.db_settings');
              ?>
            </a>
            <div id="names-container" class="fields">
                <ul id="tabs-database" class="fields form-list">
              <li>
                <div class="field-name">
                  <div class="field-title">
                    <?php print $this->Form->label('hostname',_txt('pl.rciamstatsviewer.hostname')); ?>
                    <span class="required">*</span>
                  </div>
                </div>
                <div class="field-info">
                  <?php
                  $value = empty($rciam_stats_viewers['RciamStatsViewer']['hostname']) ? ''
                          : filter_var($rciam_stats_viewers['RciamStatsViewer']['hostname'],FILTER_SANITIZE_SPECIAL_CHARS);
                  print $this->Form->input('hostname', array('size' => 50,'value' => $value));
                  ?>
                </div>
              </li>
              <li>
                <div class="field-name">
                  <div class="field-title">
                    <?php print $this->Form->label('port',_txt('fd.server.port')); ?>
                    </div>
                  </div>
                <div class="field-info">
                  <?php
                    $value = empty($rciam_stats_viewers['RciamStatsViewer']['port']) ? ''
                    : filter_var($rciam_stats_viewers['RciamStatsViewer']['port'],FILTER_SANITIZE_SPECIAL_CHARS);
                    print $this->Form->input('port', array('size' => 50,'value' => $value));
                  ?>
                </div>
              </li>
              <li>
                <div class="field-name">
                  <div class="field-title">
                    <?php print ($e ? $this->Form->label('type', _txt('fd.type')) : _txt('fd.type')); ?><span class="required">*</span>
                  </div>
                </div>
                <div class="field-info">
                  <?php
                    global $cm_lang, $cm_texts;
                    $attrs = array();
                    $attrs['value'] = (isset($rciam_stats_viewers) ? $rciam_stats_viewers['RciamStatsViewer']['type'] : '');
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
                  <div class="field-title">
                    <?php print $this->Form->label('username',_txt('fd.server.username')); ?>
                    <span class="required">*</span>
                  </div>
                </div>
                <div class="field-info">
                  <?php
                    $value = empty($rciam_stats_viewers['RciamStatsViewer']['username']) ? ''
                    : filter_var($rciam_stats_viewers['RciamStatsViewer']['username'],FILTER_SANITIZE_SPECIAL_CHARS);
                    print $this->Form->input('username', array('size' => 50,'value' => $value));
                  ?>
                </div>
              </li>
              <li>
                <div class="field-name">
                  <div class="field-title">
                    <?php print $this->Form->label('password',_txt('fd.password')); ?>
                    <span class="required">*</span>
                  </div>
                </div>
                <div class="field-info">
                  <?php
                    $value = empty($rciam_stats_viewers['RciamStatsViewer']['password']) ? ''
                    : filter_var($rciam_stats_viewers['RciamStatsViewer']['password'],FILTER_SANITIZE_SPECIAL_CHARS);
                    print $this->Form->input('password', array('size' => 50,'value' => $value));
                  ?>
                </div>
              </li>
              <li>
                <div class="field-name">
                  <div class="field-title">
                    <?php print $this->Form->label('databas',_txt('pl.rciamstatsviewer.database')); ?>
                    <span class="required">*</span>
                  </div>
                </div>
                <div class="field-info">
                  <?php
                    $value = empty($rciam_stats_viewers['RciamStatsViewer']['databas']) ? ''
                    : filter_var($rciam_stats_viewers['RciamStatsViewer']['databas'],FILTER_SANITIZE_SPECIAL_CHARS);
                    print $this->Form->input('databas', array('size' => 50,'value' => $value));
                  ?>
                </div>
              </li>
              <li>
                <div class="field-name">
                  <div class="field-title">
                    <?php print $this->Form->label('persistent',_txt('fd.server.persistent')); ?>
                    <span class="required">*</span>
                  </div>
                </div>
                <div class="field-info">
                  <?php
                    $attrs = array();
                    $attrs['value'] = (isset($rciam_stats_viewers) ? $rciam_stats_viewers['RciamStatsViewer']['persistent'] : '');
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
                  <div class="field-title">
                    <?php print $this->Form->label('encoding',_txt('fd.server.encoding')); ?>
                    <span class="required">*</span>
                  </div>
                </div>
                <div class="field-info">
                  <?php
                    $attrs = array();
                    $attrs['value'] = (isset($rciam_stats_viewers) ? $rciam_stats_viewers['RciamStatsViewer']['encoding'] : '');
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
              <li class="fields-submit">
                  <div id="connection-test-lbl" class="field-name"></div>
                  <div class="field-info">
                      <button type='button'
                              id='connection-test-btn'
                              class='ui-button ui-corner-all ui-widget'>
                        <?php print _txt('fd.server.test_connection'); ?>
                      </button>
                  </div>
              </li>
            </ul>
            </div>
          </li>
          <li id="fields-stats" class="fieldGroup">
            <a href="#tabs-stats" class="fieldGroupName">
                  <em class="material-icons">indeterminate_check_box</em>
                  <?php print _txt('pl.rciamstatsviewer.pl_config'); ?>
            </a>
            <div id="names-container" class="fields">
              <ul id="tabs-stats" class="fields form-list">
                  <li>
                    <div class="field-name">
                      <div class="field-title">
                        <?php print _txt('pl.rciamstatsviewer.statisticsTableName'); ?>
                        <span class="required">*</span>
                      </div>
                      <div class="field-desc"><?php print _txt('pl.rciamstatsviewer.statisticsTableName.desc'); ?></div>
                    </div>
                    <div class="field-info">
                      <?php
                        $value = empty($rciam_stats_viewers['RciamStatsViewer']['statisticsTableName']) ? ''
                        : filter_var($rciam_stats_viewers['RciamStatsViewer']['statisticsTableName'],FILTER_SANITIZE_SPECIAL_CHARS);
                        print $this->Form->input('statisticsTableName', array('size' => 50,'value' => $value));
                      ?>
                    </div>
                  </li>
                  <li>
                    <div class="field-name">
                      <div class="field-title">
                        <?php print _txt('pl.rciamstatsviewer.identityProvidersMapTableName'); ?>
                        <span class="required">*</span>
                      </div>
                      <div class="field-desc"><?php print _txt('pl.rciamstatsviewer.identityProvidersMapTableName.desc'); ?></div>
                    </div>
                    <div class="field-info">
                      <?php
                        $value = empty($rciam_stats_viewers['RciamStatsViewer']['identityProvidersMapTableName']) ? ''
                        : filter_var($rciam_stats_viewers['RciamStatsViewer']['identityProvidersMapTableName'],FILTER_SANITIZE_SPECIAL_CHARS);
                        print $this->Form->input('identityProvidersMapTableName', array('size' => 50,'value' => $value));
                      ?>
                    </div>
                  </li>
                  <li>
                    <div class="field-name">
                      <div class="field-title">
                        <?php print _txt('pl.rciamstatsviewer.serviceProvidersMapTableName'); ?>
                        <span class="required">*</span>
                      </div>
                      <div class="field-desc"><?php print _txt('pl.rciamstatsviewer.serviceProvidersMapTableName.desc'); ?></div>
                    </div>
                    <div class="field-info">
                      <?php
                        $value = empty($rciam_stats_viewers['RciamStatsViewer']['serviceProvidersMapTableName']) ? ''
                        : filter_var($rciam_stats_viewers['RciamStatsViewer']['serviceProvidersMapTableName'],FILTER_SANITIZE_SPECIAL_CHARS);
                        print $this->Form->input('serviceProvidersMapTableName', array('size' => 50,'value' => $value));
                      ?>
                    </div>
                  </li>
                </ul>
            </div>
          </li>
          <li id="fields-btn" class="fieldGroup">  <!-- Save Button -->
            <div id="names-container" class="fields">
              <ul class="fields form-list">
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
            </div>
          </li>
        </ul>
    </div> <!-- personExplorer -->
<!-- This is currently not supported due to a bug in COmanage. --><?php //print $this->element("changelog"); ?>
</div> <!-- explorerContainer -->
<script type="text/javascript">
    function responseConstruct(msg, status, testBtn) {
        color = (status === 'error') ? 'red' : 'green';
        text = "<span style='color:" + color + "'>" + msg + "</span>";
        $("#connection-test-lbl").html(text);

        testBtn.toggleClass('ui-state-disabled');
        testBtn.find('span').remove();
        testBtn.text("<?php print _txt('fd.server.test_connection'); ?>");
        testBtn.removeAttr('disabled');
    }
    
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
      
      // Update the default value each time i change
      $('input').on('keyup', function() {
          this.defaultValue = this.value;
      });
      $('select').on('change', function() {
          $("option[value=" + this.value + "]", this)
              .attr("selected", true).siblings()
              .removeAttr("selected");
      });

      $("#connection-test-btn").click(function(e) {
          // Toggle the button
          var testBtn = $(this);
          var loadingSpan = $("<span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span>");
          testBtn.text('Connecting..');
          testBtn.toggleClass('ui-state-disabled');
          testBtn.prepend(loadingSpan);
          testBtn.attr('disabled', true);
          
          model = '<?php print Inflector::singularize($model);?>';
          // The data we will Post to COmanage. We include the token as well.
          var db_config = {
              _Token: {}
          };
          db_config.host = $("input[name='data[" + model + "][hostname]']").attr('value');
          db_config.database = $("input[name='data[" + model + "][databas]']").attr('value');
          db_config.persistent = $("select[name='data[" + model + "][persistent]'] option:selected").attr('value');
          db_config.password = $("input[name='data[" + model + "][password]']").attr('value');
          db_config.datasource = $("select[name='data[" + model + "][type]'] option:selected").attr('value');
          db_config.encoding = $("select[name='data[" + model + "][encoding]'] option:selected").attr('value');
          db_config.login = $("input[name='data[" + model + "][username]']").attr('value');
          db_config._Token.key = '<?php echo $token_key;?>';

          $.ajax({
              type: "POST",
              url: '<?php echo $this->Html->url(array(
                'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                'controller' => 'rciam_stats_viewers',
                'action' => 'testconnection',
                'co'  => $cur_co['Co']['id'])); ?>',
              beforeSend: function(xhr) {
                  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                  xhr.setRequestHeader('X-CSRF-Token', '<?php echo $token_key;?>');
              },
              cache:false,
              data: db_config,
              success: function(response) {
                  responseConstruct(response.msg, response.status, testBtn);
              },
              error: function(jqxhr) {
                  var response = jqxhr.responseJSON;
                  responseConstruct(response.msg, response.status, testBtn);
              }
          }); // ajax
      }); // Click event
    });
</script>