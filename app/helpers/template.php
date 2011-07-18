<?php

//===============================================================
// Template
//===============================================================
class Template extends KISS_View {

	//Example of overriding a constructor/method, add some code then pass control back to parent
	function __construct($vars='') {
		$this->vars = $vars;
		$file = $this->getTemplate();
		parent::__construct($file,$vars);
	}

	function output($vars=''){
		$template = new Template($vars);
		$template->get("head");
		$template->get("section");
		$template->get("foot");
		return parent::do_dump($template->file, $template->vars);
	}
	
	function display($block='', $names=''){
		if (is_array($block))
		  foreach($block as $name=>$html)
		  	if ( !is_array($names) || (is_array($names) && array_key_exists($name, $names)) )
			  echo "$html\n";
		elseif ( is_array($names) )
		  foreach($names as $name)
		  	if ( array_key_exists($name, $block) )
			  echo "$html\n";
	}
	
	function get($name=''){
		$files = findFiles( $name.'.php' );
		if(!array_key_exists($name, $this->vars)){ $this->vars[$name] = array(); }
		foreach($files as $view){
			 
			 $this->vars[$name][] = View::do_fetch( $view, $this->vars);
		}
	}
	
	function getTemplate(){
		$file = (array_key_exists('template', $this->vars) && is_file(TEMPLATES.$this->vars['template'])) ? TEMPLATES.$this->vars['template'] : TEMPLATES.DEFAULT_TEMPLATE;
		return $file;
	}
	
}

function mainMenu(){

	if( array_key_exists('db_pages', $GLOBALS) ){
		$dbh = $GLOBALS['db_pages'];
		$sql = 'SELECT * FROM "pages" ORDER BY "date" LIMIT 5';
		$results = $dbh->query($sql);
		while ($variable = $results->fetch(PDO::FETCH_ASSOC)) {
			$data['modules']['main_menu'][] = array("title"=>$variable['title'], "path"=>$variable['path']);
		};  
		View::do_dump( getPath('views/modules/main_menu.php'), $data);
	}
}

function listTemplates( $selected=null){
	
	$data['selected']['list_templates'] = $selected;
	
	if ($handle = opendir(TEMPLATES)) {
		while (false !== ($template = readdir($handle))) {
			if ($template == '.' || $template == '..') { 
			  continue; 
			} 
			if ( is_file(TEMPLATES.$template) ) {
				$data['modules']['list_templates'][] = $template;
			}
		}	
		View::do_dump( getPath('views/modules/list_templates.php'), $data);
	}
}

?>