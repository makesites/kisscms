      <h3>Template</h3>

<?php 
if( count( $modules['list_templates'] ) > 0 ) {
?>
<select name="template">
<?
	foreach ($modules['list_templates'] as $template){
		echo '<option value="' . $template . '"';
		if( $template == $selected['list_templates'] ) {
			echo ' selected="selected"';
		}
		echo '>' . $template . '</option>';
	}
?>
</select>
<?
}
?>
