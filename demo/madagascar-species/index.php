<?php

//this file is just meant for ad hoc processing of large data sets, eventually I'd like to turn it into OO classes once we figure out some typical use cases

$specimen = file_get_contents('madagascar-1970-2014.json');

$specimen = json_decode($specimen);

$habitats = array(
		'coastal scrub',
		'coconut plantation',
		'cultivated land',
		'disturbed montane rainforest',
		'disturbed rainforest',
		'eucalyptus plantation',
		'fynbos',
		'gallery forest',
		'grassland',
		'in house',
		'littoral rainforest',
		'littoral vegetation',
		'mangrove',
		'montane rainforest',
		'montane rainforest edge',
		'montane shrubland',
		'open secondary vegetation',
		//'park/garden',
		'rainforest',
		'rainforest edge',
		'roadside',
		'savannah woodland',
		'spiny forest/thicket',
		'tropical dry forest',
		'tropical dry forest edge',
		'Uapaca woodland',
		'secondary rainforest'
	);

$species = array();
$i = 0;
foreach($specimen->specimens AS $s) {
	$species[$i]['date'] = date("Y",strtotime($s->datecollected));
	$species[$i]['name'] = $s->scientific_name;
	$species[$i]['collectioncode'] = $s->fieldNumber;
	$species[$i]['code'] = $s->catalogNumber;
	$species[$i]['elevation'] = $s->minimumElevationInMeters;
	$species[$i]['habitat'] = $s->habitat;
	$i++;
}


/*
//let's count distinct species per habitat
$distincts = array();

foreach($habitats AS $h) {

	$distincts[$h] = array();

	foreach($species AS $s) {
		if(preg_match("/$h/i", $s['habitat'])) {
			$habby = '';
			if(preg_match("/rainforest/i", $s['habitat'])) {
				if(preg_match("/montane rainforest edge/i", $s['habitat'])) {
					$habby = '--montane-edge--';
					if(!in_array($s['name'], $distincts[$h])) {
						array_push($distincts[$h], $s['name']);
					}
				}
				elseif(preg_match("/montane rainforest/i", $s['habitat'])) {
					$habby .= '--montane--';
					if(!in_array($s['name'], $distincts[$h])) {
						array_push($distincts[$h], $s['name']);
					}
				}
				elseif(preg_match("/rainforest edge/i", $s['habitat'])) {
					$habby .= '--rainforest-edge--';
					if(!in_array($s['name'], $distincts[$h])) {
						array_push($distincts[$h], $s['name']);
					}
				}
				elseif(preg_match("/secondary rainforest/i", $s['habitat'])) {
					$habby .= '--secondary--';
					if(!in_array($s['name'], $distincts[$h])) {
						array_push($distincts[$h], $s['name']);
					}
				}
				elseif(preg_match("/disturbed rainforest/i", $s['habitat'])) {
					$habby .= '--disturbed--';
					if(!in_array($s['name'], $distincts[$h])) {
						array_push($distincts[$h], $s['name']);
					}
				}
				elseif(preg_match("/littoral rainforest/i", $s['habitat'])) {
					$habby .= '--littoral--';
					if(!in_array($s['name'], $distincts[$h])) {
						array_push($distincts[$h], $s['name']);
					}
				}
				else { //just vanilla rainforest
					$habby .= '--vanilla--';
					if(!in_array($s['name'], $distincts[$h])) {
						array_push($distincts[$h], $s['name']);
					}
				}
			}
			elseif(!in_array($s['name'], $distincts[$h])) {
				array_push($distincts[$h], $s['name']);
			}
			//print '<p>' . $habby . '</p>';
		}
	}

}

$data = array();
$i = 0;
$total = 0;
foreach($distincts AS $d => $val) {
	$data[$i]['habitat'] = $d;
	$data[$i]['count'] = count($val);
	$i++;

}

$data = json_encode($data);

print $data;

*/

/*
for($i = 200; $i<=3000; $i + 200) {
	$min = $i - 200;
	$max = $i;

	$distincts[$i] = array();
	$elevations = array();

	foreach($species AS $s) {
		if( ($s['elevation'] <= $max) && ($s['elevation'] >= $min)) {
			if(!in_array($s['name'], $distincts[$i])) {
				array_push($distincts[$i], $s['name']);
				$elevations[$i][] = $s['name'];
			}
		}
	}

}
*/

//let's count distinct species per elevation 200m++ in rainforest habitat
/*
$elevations = array(
		200 => array(),
		400 => array(),
		600 => array(),
		800 => array(),
		1000 => array(),
		1200 => array(),
		1400 => array(),
		1600 => array(),
		1800 => array(),
		2000 => array(),
		2200 => array(),
		2400 => array(),
		2600 => array(),
		2800 => array(),
		3000 => array()
		);

foreach($species AS $s) {

	if($s['elevation'] < 200) {
		if(!in_array($s['name'], $elevations[200])){
			array_push($elevations[200], $s['name']);
		}

	}
	elseif($s['elevation'] < 400) {
		if(!in_array($s['name'], $elevations[400])){
			array_push($elevations[400], $s['name']);
		}
	}
	elseif($s['elevation'] < 600) {
		if(!in_array($s['name'], $elevations[600])){
			array_push($elevations[600], $s['name']);
		}
	}
	elseif($s['elevation'] < 800) {
		if(!in_array($s['name'], $elevations[800])){
			array_push($elevations[800], $s['name']);
		}
	}
	elseif($s['elevation'] < 1000) {
		if(!in_array($s['name'], $elevations[1000])){
			array_push($elevations[1000], $s['name']);
		}
	}
	elseif($s['elevation'] < 1200) {
		if(!in_array($s['name'], $elevations[1200])){
			array_push($elevations[1200], $s['name']);
		}
	}
	elseif($s['elevation'] < 1400) {
		if(!in_array($s['name'], $elevations[1400])){
			array_push($elevations[1400], $s['name']);
		}
	}
	elseif($s['elevation'] < 1600) {
		if(!in_array($s['name'], $elevations[1600])){
			array_push($elevations[1600], $s['name']);
		}
	}
	elseif($s['elevation'] < 1800) {
		if(!in_array($s['name'], $elevations[1800])){
			array_push($elevations[1800], $s['name']);
		}
	}
	elseif($s['elevation'] < 2000) {
		if(!in_array($s['name'], $elevations[2000])){
			array_push($elevations[2000], $s['name']);
		}
	}
	elseif($s['elevation'] < 2200) {
		if(!in_array($s['name'], $elevations[2200])){
			array_push($elevations[2200], $s['name']);
		}
	}
	elseif($s['elevation'] < 2400) {
		if(!in_array($s['name'], $elevations[2400])){
			array_push($elevations[2400], $s['name']);
		}
	}
	elseif($s['elevation'] < 2600) {
		if(!in_array($s['name'], $elevations[2600])){
			array_push($elevations[2600], $s['name']);
		}
	}
	elseif($s['elevation'] < 2800) {
		if(!in_array($s['name'], $elevations[2800])){
			array_push($elevations[2800], $s['name']);
		}
	}
	elseif($s['elevation'] < 3000) {
		if(!in_array($s['name'], $elevations[3000])){
			array_push($elevations[3000], $s['name']);
		}
	}

}

$data = array();
$i = 0;
foreach($elevations AS $e => $val) {
	$data[$i]['elevation'] = $e;
	$data[$i]['count'] = count($val);
	$i++;

}

$data = json_encode($data);

print '<pre>'; print_r($data); print '</pre>';
*/


$distincts = array();
$collection_events = array();
$by_year = array();
$events = array();
$specimens = array();

for($i = 1970; $i <= 2014; $i++) {
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

/*

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


//specimen added
$data = array();
$i = 0;
foreach($specimens AS $year => $val) {
	$data[$i]['year'] = $year;
	$data[$i]['count'] = count($val);
	$i++;
}

$data = json_encode($data);

print $data;

*/



?>