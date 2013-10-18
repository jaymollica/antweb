<?php

  class antweb {

    protected $_db;

    public function __construct(PDO $db) {
      $this->_db = $db;
    }

    public function getSpecimens($rank,$name) {

      if(!$name) {
        $sql = $this->_db->prepare("SELECT distinct($rank) FROM specimen ORDER BY $rank ASC");
        $sql->execute();
        if($sql->rowCount() > 0) {
          $ranks = $sql->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
          $ranks = 'No records were found.';
        }

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

      return json_encode($specimen);

    }

    public function getCoord($lat,$lon,$r) {
      $sql = $this->_db->prepare("SELECT *, ( 6371 * acos( cos( radians($lat) ) * cos( radians( decimal_latitude ) ) * cos( radians( decimal_longitude ) - radians($lon) ) + sin( radians($lat) ) * sin( radians( decimal_latitude ) ) ) ) AS distance FROM specimen HAVING distance < $r ORDER BY distance");

      $sql->execute();

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

      return json_encode($images);

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