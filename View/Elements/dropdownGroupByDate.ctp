<button type="button" class="btn btn-default dropdown-toggle filter-button" data-toggle="dropdown">
  Filter <span class="caret"></span>
</button>
<ul class="dropdown-menu">
  <?php foreach (RciamStatsViewerDateEnum::type as $type) : ?>
    <li><a href="#" onclick="return false;" class="groupDataByDate" data-value="<?php print $type; ?>"><?php print ucfirst($type); ?> Basis</a></li>
  <?php endforeach; ?>
</ul>