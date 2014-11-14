<?php

//this file is just meant for ad hoc processing of large data sets, eventually I'd like to turn it into OO classes once we figure out some typical use cases

$specimen = file_get_contents('madagascar1970-2010.json');

$specimen = json_decode($specimen);

$species = array();
$i = 0;
foreach($specimen->specimens AS $s) {
	$species[$i]['date'] = date("Y",strtotime($s->datecollected));
	$species[$i]['name'] = $s->scientific_name;
	$species[$i]['collectioncode'] = $s->fieldNumber;
	$species[$i]['code'] = $s->catalogNumber;
	$i++;
}

$distincts = array();
$collection_events = array();
$by_year = array();
$events = array();
$specimens = array();

for($i = 1970; $i <= 2010; $i++) {
	$by_year[$i] = array();
	$events[$i] = array();
	$specimens[$i] = array();
	foreach($species AS $s) {
		if($s['date'] == $i) {
			if(!in_array($s['name'], $distincts)) {
				array_push($distincts, $s['name']);
				array_push($by_year[$i], $s['name']);
			}
			if(!in_array($s['collectioncode'], $collection_events)) {
				array_push($collection_events, $s['collectioncode']);
				array_push($events[$i], $s['collectioncode']);
			}
			array_push($specimens[$i], $s['code']);
		}
	}
}

/*
$data = array();
$i = 0;
$total = 0;
foreach($by_year AS $year => $val) {
	$data[$i]['year'] = $year;
	$data[$i]['count'] = count($val);
	$total += count($val);
	$data[$i]['total'] = $total;
	$i++;
}

//print '<pre>'; print_r($data); print '</pre>';

$data = json_encode($data);

print $data;

print "<div>&nbsp;</div>";

//collection events
$data = array();
$i = 0;
foreach($events AS $year => $val) {
	$data[$i]['year'] = $year;
	$data[$i]['count'] = count($val);
	$i++;
}

$data = json_encode($data);

print $data;
*/

//collection events
$data = array();
$i = 0;
foreach($specimens AS $year => $val) {
	$data[$i]['year'] = $year;
	$data[$i]['count'] = count($val);
	$i++;
}

$data = json_encode($data);

print $data;

?>