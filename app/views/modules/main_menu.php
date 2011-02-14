      <h3>Main Menu</h3>
      <ul>
<?php 
foreach ($modules['latest_updates'] as $k=>$v){
	echo '<li><a href="' . myUrl( $v['path']) . '">' . $v['title'] . '</a></li>';
}
?>
	  </ul>
