      <h3>Main Menu</h3>
      <ul>
<?php 
foreach ($modules['main_menu'] as $v){
	echo '<li><a href="' . myUrl( $v['path'], true ) . '">' . $v['title'] . '</a></li>';
}
?>
	  </ul>
