<?php
 /**
 *
 */
ob_start();
include 'objtreedata.php';
$data = ob_get_clean();
print '</div><script>var jsTreeData = ' . $data . '; </script><div>';
//print '</ul>';
?>