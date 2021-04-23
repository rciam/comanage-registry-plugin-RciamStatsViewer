<div class="float-right" style="<?php $type == 'dashboard' ? print 'padding: 10px 0px' : print 'top: 0px; position: relative;'; ?>">
  <button type="button" class="btn btn-block btn-primary unique-logins-button">
    <input type="checkbox" id="unique-logins-<?php print $type; ?>" value="unique-logins">
    <span class="unique-logins-text">
      <?php print _txt('pl.rciamstatsviewer.uniquelogins'); ?>
      <i class="fa fa-fw fa-info-circle" data-toggle="tooltip" data-placement="bottom" title="<?php print _txt('pl.rciamstatsviewer.uniquelogins.desc'); ?>"></i>
    </span>
  </button>
</div>