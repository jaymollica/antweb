<?php

  class antweb {

    protected $_db;

    public function __construct(PDO $db) {
      $this->_db = $db;

      // a list of valid arguments to check any incoming GETs against
      $this->validArguments = array(
        'occurrenceId', //occurrenceId
        'subfamily',
        'genus',
        'species', //specificEpithet
        'type', // typeStatus
        'bbox',
        'date',  //dateIdentified
        'elevation',  //minimumElevationInMeters
        'state_province', //stateProvince
        'habitat',
        'georeferenced',
        'limit',
        'offset'
      );
    }

    public function getColumnNames($table) {

      $sql = $this->_db->prepare("DESCRIBE " . $table);
      $sql->execute();
      if($sql->rowCount() > 0) {
        $columns = $sql->fetchAll(PDO::FETCH_ASSOC);
        $fields = array();
        foreach($columns AS $c) {
          $fields[] = $c['Field'];
        }
      }

      return $fields;

    }

    public function validateDate($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    //some of the characters coming out of the db are not utf8 encoded and throwing warnings in the log
    public function utf8Scrub($array) {

      array_walk_recursive(
              $array, function (&$value) {
                  $value = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9_.\/,:?=-]/',' ', urldecode(html_entity_decode(strip_tags($value))))));
                  $value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
              }
      );

      return $array;

    }

    //for the sake of simplicity, the argument names are not necessarily the same as their corresponding field names
    //some arguments also need to be massaged into correct formats
    public function prepareArguments($args) {

      if(isset($args['type'])) {
        $args['typeStatus'] = $args['type'];
        unset($args['type']);
      }

      if(isset($args['species'])) {
        $args['specificEpithet'] = $args['species'];
        unset($args['species']);
      }

      if(isset($args['state_province'])) {
        $args['stateProvince'] = $args['state_province'];
        unset($args['state_province']);
      }

      if(isset($args['code'])) {
        $args['occurrenceId'] = $args['code'];
        unset($args['code']);
      }

      if(isset($args['habitat'])) {
        $args['habitat'] = "%" . $args['habitat'] . "%";
      }

      if(isset($args['bbox'])) {
        $coords = explode(',', $args['bbox']);
        if(count($coords) == 4) {
          $x['lat1'] = $coords[0];
          $y['lon1'] = $coords[1];
          $x['lat2'] = $coords[2];
          $y['lon2'] = $coords[3];

          asort($x);
          asort($y);

          $args['xcoord'] = $x;
          $args['ycoord'] = $y;

        }
        
        unset($args['bbox']);
      }

      if(isset($args['date'])) {
        $dates = explode(',', $args['date']);

        if(isset($dates[1])) {
            $args['dateIdentified']['start_date'] = $dates[0];
            $args['dateIdentified']['end_date'] = $dates[1];
        }
        else {
          if($this->validateDate($dates[0], $format = 'Y-m-d')) {
            $args['dateIdentified'] = $dates[0];
          }          
        }
        unset($args['date']);
      }

      if(isset($args['elevation'])) {
        $elevs = explode(',', $args['elevation']);

        if(isset($elevs[1])) {
            $args['minimumElevationInMeters']['low_bound'] = $elevs[0];
            $args['minimumElevationInMeters']['high_bound'] = $elevs[1];
        }
        else {
          $args['minimumElevationInMeters'] = $elevs[0];
        }
        unset($args['elevation']);
      }

      if(isset($args['georeferenced'])) {
        $georeferenced = $args['georeferenced'];
        if(!is_null($georeferenced)) {
          $limits['georeferenced'] = 1;
        }
        unset($args['georeferenced']);
      }

      if(isset($args['limit']) ) {
        $limits['limit'] = $args['limit'];
        unset($args['limit']);
      }

      if(isset($args['offset'])) {
        $limits['offset'] = $args['offset'];
        unset($args['offset']);
      }

      $sql_const['args'] = $args;
      if(isset($limits)) $sql_const['limits'] = $limits;

      return $sql_const;

    }

    public function getSpecimens($arguments) {

      //validate arg for available field names
      $args = array();
      foreach($arguments AS $arg => $val) {
        if(in_array($arg,$this->validArguments)) {
          $args[$arg] = $val;
        }
      }

      //validate args for allowed characters
      foreach($args AS &$arg) {
        $aValid = array('-','_',',','.',':');
        if(!ctype_alnum(str_replace($aValid,'',$arg))) {
          $arg = 'invalid';
        }
      }

      $sql_const = $this->prepareArguments($args);
      $args = $sql_const['args'];
      $limits = $sql_const['limits'];

      $sql = "SELECT occurrenceId,
                     catalogNumber,
                     family,
                     subfamily,
                     genus,
                     specificEpithet,
                     scientific_name,
                     typeStatus,
                     stateProvince,
                     country,
                     decimalLatitude,
                     decimalLongitude,
                     dateIdentified,
                     habitat,
                     minimumElevationInMeters

                     FROM darwin_core_2 WHERE 1";

      $params = array();
      foreach($args AS $arg => $val) {
        if(!empty($arg)) {
          if(is_array($val)) {
            foreach($val AS $k => $v) {
              $params[$k] = $v;
            }
          }
          else {
            $params[$arg] = $val;
          }
        }
      }

      foreach($args AS $key => $val) {
        if($key == 'dateIdentified' && is_array($val)) {
          foreach($val AS $bound => $date) {
            if($bound == 'start_date') {
              $sql .= sprintf(' AND `%s` >= :%s',$key,$bound);
            }
            elseif($bound == 'end_date') {
              $sql .= sprintf(' AND `%s` <= :%s',$key,$bound);
            }
          }
        }
        elseif($key == 'xcoord') {
          $sql .= ' AND decimalLatitude BETWEEN';
          $i = 0;
          foreach($val AS $coord => $val) {
            if($i == 0) {
              $sql .= sprintf(' :%s',$coord);
              $i++;
            }
            else {
              $sql .= sprintf(' AND :%s',$coord);
              $i = 0;
            }
          }
        }
        elseif($key == 'ycoord') {
          $sql .= ' AND decimalLongitude BETWEEN';
          $i = 0;
          foreach($val AS $coord => $val) {
            if($i == 0) {
              $sql .= sprintf(' :%s',$coord);
              $i++;
            }
            else {
              $sql .= sprintf(' AND :%s',$coord);
              $i = 0;
            }
          }
        }
        elseif($key == 'minimumElevationInMeters' && is_array($val)) {
          foreach($val AS $bound => $date) {
            if($bound == 'low_bound') {
              $sql .= sprintf(' AND `%s` >= :%s',$key,$bound);
            }
            elseif($bound == 'high_bound') {
              $sql .= sprintf(' AND `%s` <= :%s',$key,$bound);
            }
          }
        }
        elseif($key == 'habitat') {
          $sql .= ' AND `habitat` LIKE :habitat';
        }
        else {
          $sql .= sprintf(' AND `%s` = :%s',$key,$key);
        }
      }

      if(isset($limits['georeferenced']) && $limits['georeferenced'] == 1) {
        $sql .= " AND decimalLatitude IS NOT NULL";
      }

      $sqlLim = $sql;
      if(isset($limits) && !empty($limits)) {
        if(isset($limits['limit'])) {

          $limit = $limits['limit'];
          $sqlLim .= " LIMIT $limit";
          if(isset($limits['offset'])) {
            $offset = $limits['offset'];
            $sqlLim .= " OFFSET $offset";
          }
        }
      }

      $stmt = $this->_db->prepare($sql);
      $stmtLim = $this->_db->prepare($sqlLim);

      foreach ($params as $key => $val) {
        // Using bindValue because bindParam binds a reference, which is
        // only evaluated at the point of execute
        $stmt->bindValue(':'.$key, $val);
        $stmtLim->bindValue(':'.$key, $val);

      }

      $stmt->execute();
      $stmtLim->execute();

      $totalRex = $stmt->rowCount();

      if($stmtLim->rowCount() > 0) {
        $specimens = $stmtLim->fetchAll(PDO::FETCH_ASSOC);

        foreach($specimens AS &$s) {
          if(!is_null($s['decimalLatitude'])) {
            $geojson = array(
              'type' => 'point',
              'coord' => array(
                  $s['decimalLatitude'],
                  $s['decimalLongitude']
                )
            );

            unset($s['decimalLongitude']);
            unset($s['decimalLatitude']);

            $s['geojson'] = $geojson;

          }

          $url = 'http://antweb.org/api/?occurrenceId=' . $s['occurrenceId'];
          $s = array('url' => $url) + $s;
          unset($s['occurrenceId']);

        }

        $i = 0;
        foreach($specimens AS &$s) {
          $code = $s['catalogNumber'];
          //$code = preg_replace('/-/','',$code);
          if($this->getImages($code)) {
             $s['images'] = $this->getImages($code);
          }
        }
      }
      else {
        $specimens = array('empty_set' => 'No records found.');
        //http_response_code(204);
      }

      $results['count'] = $totalRex;

      if(isset($limit)) $results['limit'] = $limit;
      if(isset($offset)) $results['offset'] = $offset;

      $results['specimens'] = $this->utf8Scrub($specimens);

      return json_encode($results);

    }

    public function getImages($code) {
      $imgQuery = $this->_db->prepare("SELECT uid,shot_type,upload_date,shot_number,has_tiff FROM image WHERE image_of_id=? ORDER BY shot_number ASC");
      $imgQuery->execute(array($code));

      if($imgQuery->rowCount() > 0) {
        $imgs = $imgQuery->fetchAll(PDO::FETCH_ASSOC);
        foreach($imgs AS $img) {

          $shot_number = $img['shot_number'];
          $type = $img['shot_type'];

          $images[$shot_number]['upload_date'] = $img['upload_date'];

          $images[$shot_number]['shot_types'][$type]['img'][] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_high.jpg';
          $images[$shot_number]['shot_types'][$type]['img'][] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_low.jpg';
          $images[$shot_number]['shot_types'][$type]['img'][] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_med.jpg';
          $images[$shot_number]['shot_types'][$type]['img'][] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_thumbview.jpg';

        }
      }
      else {
        $images = NULL;
      }

      return $images;

    }

  }

?>
