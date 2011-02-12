<?php
function _resetdb() {
  $data['foot'][]='<script type="text/javascript">
if (confirm("Really reset database?"))
  window.location="'.myUrl('ops/resetdb').'";
else
  window.location="'.myUrl('main').'";
</script>
';
  $data['body'][]='<h2>Reset Database</h2>';
  $data['body'][]='<p><strong>This will clear all existing user data and re-populate with some test data!</strong></p>';
  View::do_dump(VIEW_PATH.'layouts/mainlayout.php',$data);
}