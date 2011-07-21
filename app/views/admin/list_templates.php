<h3>Template</h3>

<?php 
if( count( $template['list'] ) > 0 ) {
?>
<select name="template">
<?
	foreach ($template['list'] as $template){
		echo '<option value="' . $template . '"';
		if( $template == $template['selected'] ) {
			echo ' selected="selected"';
		}
		echo '>' . $template . '</option>';
	}
?>
</select>
<?
}
?>
