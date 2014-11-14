<?php

	require 'config.php';

	$sql = "SELECT distinct(subfamilia) FROM ants";
	$res = mysqli_query($link,$sql);

	$i = 0;
	$subfamilia = array();
	while($s = mysqli_fetch_assoc($res)) {
		$subfamilia[$i]['name'] = $s['subfamilia'];
		$i++;
	}
	
	
	$n = 0;
	foreach($subfamilia AS &$s) {
		$sname = $s['name'];

		$sql = "SELECT distinct(genus) FROM ants WHERE subfamilia='$sname'";
		$res = mysqli_query($link,$sql);

		$i = 0;
		$genus = array();
		while($g = mysqli_fetch_assoc($res)) {
		
			$genus[$i]['name'] = $g['genus'];
			$gname = $g['genus'];
		
		
			$psql = "SELECT distinct(species) FROM ants WHERE genus='$gname'";
			$pres = mysqli_query($link,$psql);

			$j = 0;
			$species = array();
			while($p = mysqli_fetch_assoc($pres)) {

				$species['name'] = $gname . ' ' . $p['species'];
				$species['size'] = 5;

				$genus[$i]['children'][$j] = $species;
				$j++;
			}
				
			if(trim($genus[$i]['name'])!='') {
				$s['children'][$i] = $genus[$i];
			}

			$i++;	
			
		}

		if(!isset($s['children'])) {
			$s['size'] = 550;
		}

		$n++;

	}

	$sql = "SELECT distinct(species), genus FROM ants";
	$res = mysqli_query($link,$sql);

	$i = 0;
	$species = array();
	
	$ants['name'] = 'forminidae';
	$ants['children'] = $subfamilia;
	$json = json_encode($ants);
	$error = json_last_error();
	var_dump($json, $error === JSON_ERROR_UTF8);

	print '<pre>'; print_r($ants); print '</pre>';
	print '<pre>'; print_r($json); print '</pre>';

?>