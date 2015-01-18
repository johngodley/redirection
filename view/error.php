<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><script type="text/javascript" charset="utf-8">
	if (document.getElementById ('ajax_error'))
		$('ajax_error').innerHTML = '<?php printf ("%s - %s, %s", $message, $file, $line); ?>';
	else
		alert ('<?php printf ("%s - %s, %s", $message, $file, $line); ?>');
</script>