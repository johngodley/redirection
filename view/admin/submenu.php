<ul id="subsubmenu">
  <li><a <?php if (!isset($_GET['sub'])) echo 'class="current"'; ?>href="<?php echo $url ?>">Redirection</a></li>
  <li><a <?php if ($_GET['sub'] == 'log') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=log">Log</a></li>
  <li><a <?php if ($_GET['sub'] == '404') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=404">404 Log</a></li>
  <li><a <?php if ($_GET['sub'] == 'options') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=options">Options</a></li>
  <!-- <li><a <?php if ($_GET['sub'] == 'import') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=import">Import/Export</a></li> -->
</ul>