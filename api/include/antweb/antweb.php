<?php

  class antweb {

    protected $_db;

    public function __construct(PDO $db) {
      $this->_db = $db;

      // a list of valid arguments to check any incoming GETs against
      $this->validArguments = array(
        'subfamily',
        'genus',
        'species', //specificEpithet
        'type', // typeStatus
        'bbox',
        'date',  //dateIdentified
        'elevation',  //minimumElevationInMeters
        'state_province', //stateProvince
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

    //some of the characters coming out of the db are not utf8 encoded and throwing warnings in the log
    public function utf8Scrub($array) {

      array_walk_recursive(
              $array, function (&$value) {
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

      return $args;

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
        $aValid = array('-','_');
        if(!ctype_alnum(str_replace($aValid,'',$arg))) {
          $arg = 'invalid';
        }
      }

      print '<pre>'; print_r($this->validArguments); print '</pre>';
      $args = $this->prepareArguments($args);
      print '<pre>'; print_r($args); print '</pre>';

      $sql = "SELECT * FROM darwin_core_2 WHERE 1";


      $params = array();
      foreach($args AS $arg => $val) {
        if(!empty($arg)) {
          $params[$arg] = $val;
        }
      }

      foreach($params AS $key => $val) {
        $sql .= sprintf(' AND `%s` = :%s',$key,$key);
      }

      print '<pre>'; print_r($params); print '</pre>';

      $stmt = $this->_db->prepare($sql);

      foreach ($params as $key => $val) {
        // Using bindValue because bindParam binds a reference, which is
        // only evaluated at the point of execute
        $stmt->bindValue(':'.$key, $val);
      }

      $stmt->execute();

      if($stmt->rowCount() > 0) {
        $specimens = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }
      else {
        $specimens = array('No records found.');
        //http_response_code(204);
      }

      $specimens = $this->utf8Scrub($specimens);
      return json_encode($specimens);

      //$sql->execute(array($genus,$species));

    }

    /*

    public function getSpecies($genus,$species) {

      if(!ctype_alnum($genus) || !ctype_alnum($species)) {
        exit;
      }

      $sql = $this->_db->prepare("SELECT * FROM specimen WHERE genus=? AND species=?");
      $sql->execute(array($genus,$species));

      if($sql->rowCount() > 0) {
        $specimens = $sql->fetchAll(PDO::FETCH_ASSOC);
      }
      else {
        $specimens = 'No records found.';
        //http_response_code(204);
      }

      $specimens = $this->utf8Scrub($specimens);
      return json_encode($specimens);

    }

    public function getSpecimens($rank,$name) {

      $fields = $this->getColumnNames('specimen');

      if (!in_array($rank, $fields)) {
      exit;
      }

      if(!$name) {
        $sql = $this->_db->prepare("SELECT distinct($rank) FROM specimen ORDER BY $rank ASC");
        $sql->execute();
        if($sql->rowCount() > 0) {
          $ranks = $sql->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
          $ranks = 'No records found.';
        }

        $ranks = $this->utf8Scrub($ranks);
        return json_encode($ranks);
      }
      else {
        $sql = $this->_db->prepare("SELECT * FROM specimen WHERE $rank=?");
        $sql->execute(array($name));
        if($sql->rowCount() > 0) {
          $specimens = $sql->fetchAll(PDO::FETCH_ASSOC);

          $i = 0;

          foreach($specimens AS $s) {

            $specimen[$i]['meta'] = $s;

            if($this->getImages($s['code'])) {
              $specimen[$i]['images'] = $this->getImages($s['code']);
            }

            $i++;

          }

        }
        else {
        $specimen = 'No records found.';
        }

      }

      $specimen = $this->utf8Scrub($specimen);
      return json_encode($specimen);

    }

    public function getSpecific($code) {

      $sql = $this->_db->prepare("SELECT * FROM specimen WHERE code=?");
      $sql->execute(array($code));
      if($sql->rowCount() > 0) {
          $specimen['meta'] = $sql->fetchAll(PDO::FETCH_ASSOC);

        if($this->getImages($code)) {
          $specimen['images'] = $this->getImages($code);
        }

        //  return json_encode($specimen);

      }
      else {
        $specimen = 'No records found.';
      }

      $specimen = $this->utf8Scrub($specimen);
      return json_encode($specimen);

    }

    public function getCoord($lat,$lon,$r) {

      if( (!is_numeric($r)) || (!is_numeric($lat)) || (!is_numeric($lon)) ) {
        exit;
      }

      $sql = $this->_db->prepare("SELECT *, ( 6371 * acos( cos( radians(:lat) ) * cos( radians( decimal_latitude ) ) * cos( radians( decimal_longitude ) - radians(:lon) ) + sin( radians(:lat) ) * sin( radians( decimal_latitude ) ) ) ) AS distance FROM specimen HAVING distance < $r ORDER BY distance");

      $sql->execute(array(':lat' => $lat, ':lon' => $lon));

      if($sql->rowCount() > 0) {

        $specimens = $sql->fetchAll(PDO::FETCH_ASSOC);

        $i = 0;

          foreach($specimens AS $s) {

            $specimen[$i]['meta'] = $s;

            if($this->getImages($s['code'])) {
              $specimen[$i]['images'] = $this->getImages($s['code']);
            }

            $i++;

          }

      }

      $specimen = $this->utf8Scrub($specimen);
      return json_encode($specimen);

    }

    public function getSpecimensCreatedAfter($days) {

      $since = date('Y-m-d', strtotime("-$days days"));

      $sql = $this->_db->prepare("SELECT * FROM specimen WHERE datedetermined>=?");
      $sql->execute(array($since));

      if($sql->rowCount() > 0) {

        $specimens = $sql->fetchAll(PDO::FETCH_ASSOC);

        $i = 0;

          foreach($specimens AS $s) {

            $specimen[$i]['meta'] = $s;

            if($this->getImages($s['code'])) {
              $specimen[$i]['images'] = $this->getImages($s['code']);
            }

            $i++;

          }

      }
      else {
        $specimen = 'No records found.';
      }

      $specimen = $this->utf8Scrub($specimen);
      return json_encode($specimen);

    }

    public function getImagesAddedAfter($days,$type) {

      $since = date('Y-m-d', strtotime("-$days days"));

      if($type) {
        $sql = $this->_db->prepare("SELECT * FROM image WHERE upload_date>=? AND shot_type=? ORDER BY shot_number ASC");
        $sql->execute(array($since,$type));
      }
      else {
        $sql = $this->_db->prepare("SELECT * FROM image WHERE upload_date>=? ORDER BY shot_number ASC");
        $sql->execute(array($since));
      }

      if($sql->rowCount() > 0) {
        $imgs = $sql->fetchAll(PDO::FETCH_ASSOC);

        foreach($imgs AS $img) {

          $code = $img['image_of_id'];
          $type = $img['shot_type'];

          $shot_number = $img['shot_number'];

          $images[$code][$shot_number]['upload_date'] = $img['upload_date'];

          $images[$code][$shot_number]['shot_types'][$type]['img'][] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_high.jpg';
          $images[$code][$shot_number]['shot_types'][$type]['img'][] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_low.jpg';
          $images[$code][$shot_number]['shot_types'][$type]['img'][] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_med.jpg';
          $images[$code][$shot_number]['shot_types'][$type]['img'][] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_thumbview.jpg';

        }

      }
      else {
        $images = NULL;
      }

      $images = $this->utf8Scrub($images);
      return json_encode($images);

    }

    */

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
