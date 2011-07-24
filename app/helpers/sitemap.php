<?php

class Sitemap {
	
	public $isoLastModifiedSite = "";

	function __contruct(){ 
		// search terms	
		$quotes = explode('"', $q);
		$quoted = array();
		for($i=0; $i<count($quotes); $i++){
			if($i%2==0) continue;
			$j = '"'.$quotes[$i].'"';
			array_push($quoted, $j);
			$q = str_replace($j, "", $q);
		}
		
		$searchTerms = explode("|", implode("|", explode("%20", implode("|", explode(" ", implode("|", explode("+", trim($q))))))));
		if(count($searchTerms)==1 && $searchTerms[0]=="") $searchTerms = array();
		
		$includes = array();
		$excludes = array();
		for($i=0; $i<count($searchTerms); $i++){
			if(strpos($searchTerms[$i], "-")===0){
				array_push($excludes, substr($searchTerms[$i], 1, strlen($searchTerms[$i])));
			}else{
				array_push($includes, $searchTerms[$i]);
			}
		}
		array_splice($includes, 0, 0, $quoted);
		
		// prepare search terms for display
		if(count($searchTerms) == 0 && $ingredient != "") $searchTerms = array($ingredient);
		$concatTerms = implode(", ", $includes);
		$concatTerms = str_replace(",,", ",", $concatTerms);
		if(count($excludes)>0) $concatTerms .= ", -".implode(", -", $excludes);
		
		$md = new MetaData();
		$results = new Search();
		
		
		
		// for the clear all
		$isParameter = ($preptime!="" || $cooktime!="" || $serves!="" || $rating!="" || $cuisine!="" || $brand!="" || $prod!="" || $ingredient!="" || $category!="" || $tag);
		
		// for title
		array_splice($searchTerms, 0, 0, $quoted);
		
		
		// Create recorde for homepage
		$urlsetValue .= $this->makeUrlTag ($myUrl("/"), $this->isoLastModifiedSite, "daily", "1.0");
		
		// Create records for main categories 
		foreach($md->categoryList as $category){
			$urlsetValue .= $this->makeUrlTag ($myUrl("/") ."recipes/?category=".$category, $this->isoLastModifiedSite, "weekly", "0.9");
		}
											
		// Create records for recipies
		foreach($results->matches as $recipe){ 
			$isRecipe = ($recipe["type"] == "recipe");
			$match = $recipe["ob"];
			$urlsetValue .= $this->makeUrlTag ($myUrl("/") . $match->friendlyUrl(), $this->isoLastModifiedSite, "monthly", "0.6");
		} 
		
		$XML = $xmlHeader . $urlsetOpen . $urlsetValue . $urlsetClose;
		$this->makeXMLSitemap($XML);
		
	}

	
	function makeUrlString ($urlString) {
		return htmlentities($urlString, ENT_QUOTES, 'UTF-8');
	}
	
	function makeIso8601TimeStamp ($dateTime) {
		if (!$dateTime) {
			$dateTime = date('Y-m-d H:i:s');
		}
		if (is_numeric(substr($dateTime, 11, 1))) {
			$isoTS = substr($dateTime, 0, 10) ."T"
					 .substr($dateTime, 11, 8) ."+00:00";
		}
		else {
			$isoTS = substr($dateTime, 0, 10);
		}
		return $isoTS;
	}

	function makeUrlTag ($url, $modifiedDateTime, $changeFrequency, $priority) {
		
		$urlOpen = "\t" . "<url>" . "\n";
		$urlValue = "";
		$urlClose = "\t" . "</url>" . "\n";
		$locOpen = "\t\t" . "<loc>";
		$locValue = "";
		$locClose = "</loc>" . "\n";
		$lastmodOpen = "\t\t" . "<lastmod>";
		$lastmodValue = "";
		$lastmodClose = "</lastmod>" . "\n";
		$changefreqOpen = "\t\t" . "<changefreq>";
		$changefreqValue = "";
		$changefreqClose = "</changefreq>" . "\n";
		$priorityOpen = "\t\t" . "<priority>";
		$priorityValue = "";
		$priorityClose = "</priority>" . "\n";
	
		$urlTag = $urlOpen;
		$urlValue     = $locOpen .makeUrlString("$url") .$locClose;
		if ($modifiedDateTime) {
		 $urlValue .= $lastmodOpen .makeIso8601TimeStamp($modifiedDateTime) .$lastmodClose;
		}
		if ($changeFrequency) {
		 $urlValue .= $changefreqOpen .$changeFrequency .$changefreqClose;
		}
		if ($priority) {
		 $urlValue .= $priorityOpen .$priority .$priorityClose;
		}
		$urlTag .= $urlValue;
		$urlTag .= $urlClose;
		return $urlTag;
	}
	
	function makeXMLSitemap($content){
	
		$file = fopen($_SERVER['DOCUMENT_ROOT'].WEB_FOLDER.'sitemap.xml',"w");
		fwrite($file,$content);
		fclose($file);
		
		// create the compressed version 
		$gz_content = gzcompress($content, 9); 
		$gz_file = gzopen('sitemap.xml.gz', "w9");
		gzwrite($gz_file, $gz_content);
		gzclose($gz_file);
		
		// view the Sitemap XML
		//header('Location: ./sitemap.xml');
	}

}
?>					
