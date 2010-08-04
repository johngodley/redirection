<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<ul <?php echo $class ?>>
  <li><a <?php if (!isset($_GET['sub'])) echo 'class="current"'; ?>href="<?php echo $url ?><?php if (isset ($_GET['id'])) echo '&amp;id='.urlencode( $_GET['id'] ) ?>"><?php _e ('Redirects', 'redirection'); ?></a><?php echo $trail ?></li>
  <li><a <?php if (isset($_GET['sub']) && $_GET['sub'] == 'groups') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=groups<?php if (isset ($_GET['id'])) echo '&amp;id='.$_GET['id'] ?>"><?php _e ('Groups', 'redirection'); ?></a><?php echo $trail ?></li>
  <li><a <?php if (isset($_GET['sub']) && $_GET['sub'] == 'modules') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=modules"><?php _e ('Modules', 'redirection'); ?></a><?php echo $trail ?></li>
  <li><a <?php if (isset($_GET['sub']) && $_GET['sub'] == 'log') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=log"><?php _e ('Log', 'redirection'); ?></a><?php echo $trail ?></li>
  <li><a <?php if (isset($_GET['sub']) && $_GET['sub'] == 'options') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=options"><?php _e ('Options', 'redirection'); ?></a><?php echo $trail ?></li>
  <li><a <?php if (isset($_GET['sub']) && $_GET['sub'] == 'support') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=support"><?php _e ('Support', 'redirection'); ?></a></li>
</ul>

