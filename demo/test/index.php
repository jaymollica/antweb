<?php

$specimen = file_get_contents('specimen.json');

$specimen = json_decode($specimen);

$species = array();
$i = 0;
foreach($specimen->specimens AS $s) {
	$species[$i]['date'] = date("Y",strtotime($s->datecollected));
	$species[$i]['name'] = $s->scientific_name;
	$i++;
}

$distincts = array();
$by_year = array();

for($i = 1970; $i <= 2010; $i++) {
	$by_year[$i] = array();
	foreach($species AS $s) {
		if($s['date'] == $i) {
			if(!in_array($s['name'], $distincts)) {
				array_push($distincts, $s['name']);
				array_push($by_year[$i], $s['name']);
			}
		}
	}
}

$data = array();
$i = 0;
	foreach($by_year AS $year => $val) {
		$data[$i]['year'] = $year;
		$data[$i]['count'] = count($val);
		$i++;
	}

//print '<pre>'; print_r($data); print '</pre>';

$data = json_encode($data);

print $data;

?>