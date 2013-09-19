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

          return $ranks;
        }
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

          return $specimen;

        }
      }

    }

    public function getSpecific($code) {
      $sql = $this->_db->prepare("SELECT * FROM specimen WHERE code=?");
      $sql->execute(array($code));
      if($sql->rowCount() > 0) {
          $specimen['meta'] = $sql->fetchAll(PDO::FETCH_ASSOC);

        if($this->getImages($code)) {
          $specimen[$i]['images'] = $this->getImages($code);
        } 

          return $specimen;
        //  return json_encode($specimen);

      }
    }

    public function getCoord($lat,$lon,$r) {
      $sql = $this->_db->prepare("SELECT *, ( 3959 * acos( cos( radians($lat) ) * cos( radians( decimal_latitude ) ) * cos( radians( decimal_longitude ) - radians($lon) ) + sin( radians($lat) ) * sin( radians( decimal_latitude ) ) ) ) AS distance FROM specimen HAVING distance < $r ORDER BY distance");

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

        return $specimen;

      }

    }

    public function getImages($code) {
      $imgQuery = $this->_db->prepare("SELECT uid,shot_type,upload_date,shot_number,has_tiff FROM image WHERE image_of_id=?");
      $imgQuery->execute(array($code));
      
      if($imgQuery->rowCount() > 0) {
        $imgs = $imgQuery->fetchAll(PDO::FETCH_ASSOC);
        foreach($imgs AS $img) {
          
          $images[] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_high.jpg';
          $images[] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_low.jpg';
          $images[] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_med.jpg';
          $images[] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '_' . $img['shot_number'] . '_thumbview.jpg';

          if($img['has_tiff']) {
            $images[] = 'http://www.antweb.org/images/' . $code . '/' . $code . '_' . $img['shot_type'] . '.tif';
          }

        }
      }
      else {
        $images = NULL;
      }

      return $images;

    }

  }

?>