<h3>Main Menu</h3>
<ul>
<?
if( array_key_exists('db_pages', $GLOBALS) ){
	$dbh = $GLOBALS['db_pages'];
	$sql = 'SELECT * FROM "pages" ORDER BY "date" LIMIT 5';
	$results = $dbh->query($sql);
	while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
		echo '<li><a href="' . myUrl( $v['path'], true ) . '">' . $v['title'] . '</a></li>';
	};  
}
?>
</ul>
