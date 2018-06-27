<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 6/27/2018
 * Time: 2:56 PM
 */

namespace catalog;


class catalog
{
  public function saveCatalog($room_id, $cab_list) {
    global $dbconn;

    $room_id = sanitizeInput($room_id);
    $catalog_id = 1;
    $out_id = null;

    $existing_qry = $dbconn->query("SELECT * FROM pricing_cabinet_list WHERE room_id = $room_id");

    if($existing_qry->num_rows > 0) {
      $existing = $existing_qry->fetch_assoc();

      $result = $dbconn->query("UPDATE pricing_cabinet_list SET cabinet_list = '$cab_list', catalog_id = $catalog_id WHERE id = {$existing['id']}");

      $out_id = $existing['id'];
    } else {
      $result = $dbconn->query("INSERT INTO pricing_cabinet_list (room_id, user_id, catalog_id, cabinet_list) VALUES ($room_id, {$_SESSION['shop_user']['id']}, $catalog_id, '$cab_list')");

      $out_id = $dbconn->insert_id;
    }

    if(!$result) {
      dbLogSQLErr($dbconn);
    }

    return $out_id;
  }
}