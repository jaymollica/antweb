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

            $uidQuery = $this->_db->prepare("SELECT uid FROM image WHERE image_of_id=?");
            $uidQuery->execute(array($s['code']));
            if($uidQuery->rowCount() > 0) {
              $uids = $uidQuery->fetchAll(PDO::FETCH_ASSOC);
              foreach($uids AS $uid) {
                if($uid['uid'] < 500000) {
                  $imgQuery = $this->_db->prepare("SELECT * FROM image_catalog2 WHERE id=?");
                  
                }
                else {
                  $imgQuery = $this->_db->prepare("SELECT * FROM image_catalog WHERE id=?");
                }
                $imgQuery->execute(array($uid['uid']));
                $imgFields = $imgQuery->fetchAll(PDO::FETCH_ASSOC);
                $specimen[$i]['images'][] = 'http://www.antweb.org/images/' . $imgFields[0]['dir_name'] . '/' . $imgFields[0]['image_name'];
              }

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

          $uidQuery = $this->_db->prepare("SELECT uid FROM image WHERE image_of_id=?");
          $uidQuery->execute(array($code));
          if($uidQuery->rowCount() > 0) {
            $uids = $uidQuery->fetchAll(PDO::FETCH_ASSOC);
            foreach($uids AS $uid) {
              if($uid['uid'] < 500000) {
                $imgQuery = $this->_db->prepare("SELECT * FROM image_catalog2 WHERE id=?");
                
              }
              else {
                $imgQuery = $this->_db->prepare("SELECT * FROM image_catalog WHERE id=?");
              }
              $imgQuery->execute(array($uid['uid']));
              $imgFields = $imgQuery->fetchAll(PDO::FETCH_ASSOC);
              $specimen['images'][] = 'http://www.antweb.org/images/' . $imgFields[0]['dir_name'] . '/' . $imgFields[0]['image_name'];
            }
          }
          else {
            $specimen['images'] = 'No images available';
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

            $uidQuery = $this->_db->prepare("SELECT uid FROM image WHERE image_of_id=?");
            $uidQuery->execute(array($s['code']));
            if($uidQuery->rowCount() > 0) {
              $uids = $uidQuery->fetchAll(PDO::FETCH_ASSOC);
              foreach($uids AS $uid) {
                if($uid['uid'] < 500000) {
                  $imgQuery = $this->_db->prepare("SELECT * FROM image_catalog2 WHERE id=?");
                  
                }
                else {
                  $imgQuery = $this->_db->prepare("SELECT * FROM image_catalog WHERE id=?");
                }
                $imgQuery->execute(array($uid['uid']));
                $imgFields = $imgQuery->fetchAll(PDO::FETCH_ASSOC);
                $specimen[$i]['images'][] = 'http://www.antweb.org/images/' . $imgFields[0]['dir_name'] . '/' . $imgFields[0]['image_name'];
              }

            }

            $i++;

          } 

        return $specimen;

      }

    }

  }

?>