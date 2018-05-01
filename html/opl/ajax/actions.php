<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

switch($_REQUEST['action']) {
  case 'save':
    $user = 42;
    $opl = $_REQUEST['opl'];

    $opl_qry = $dbconn->query("SELECT oplu.id, oplu.opl, u.name FROM opl_users oplu LEFT JOIN user u on oplu.user_id = u.id WHERE user_id = $user");

    if($opl_qry->num_rows !== 0) {
      $opl_result = $opl_qry->fetch_assoc();

      $update = $dbconn->prepare('UPDATE opl_users SET opl = ?, last_modified_by = ? WHERE id = ?');
      $update->bind_param('sii', $opl,$_SESSION['userInfo']['id'], $opl_result['id']);

      if($update->execute()) {
        $history = $dbconn->prepare('INSERT INTO opl_history (user_id, updated_by, opl, timestamp) VALUES (?, ?, ?, UNIX_TIMESTAMP())');
        $history->bind_param('iis', $user, $_SESSION['userInfo']['id'], $opl_result['opl']);
        $history->execute();

        echo displayToast('success', "Updated OPL for {$opl_result['name']}.", 'Updated OPL');
      } else {
        dbLogSQLErr($dbconn);
      }
    } else {
      $insert = $dbconn->prepare('INSERT INTO opl_users (user_id, last_modified_by, opl) VALUES (?, ?, ?)');
      $insert->bind_param('iss', $user, $_SESSION['userInfo']['id'], $opl);

      if($insert->execute()) {
        echo displayToast('success', 'Created OPL.', 'Created OPL');
      } else {
        dbLogSQLErr($dbconn);
      }
    }

    break;
  case 'getOPL':
    $opl_qry = $dbconn->query("SELECT opl FROM opl_users WHERE user_id = 42");

    if($opl_qry->num_rows !== 0) {
      $opl = $opl_qry->fetch_assoc();

      if($opl['opl'] !== '{"key": "root_1", "title": "root", "partsel": true, "expanded": true, "selected": true}') {
        echo $opl['opl'];
      } else {
        echo '{"key": "root_1", "title": "root", "partsel": false, "children": [{"key": "_2", "title": "Default", "folder": true, "partsel": false, "selected": false}], "expanded": true, "selected": false}';
      }
    } else {
      echo '{"key": "root_1", "title": "root", "partsel": false, "children": [{"key": "_2", "title": "Default", "folder": true, "partsel": false, "selected": false}], "expanded": true, "selected": false}';
    }

    break;
  case 'getOPLModifiedBy':
    $opl_user_id = sanitizeInput($_REQUEST['opl_user_id']);

    $opl_qry = $dbconn->query("SELECT u.name FROM opl_users oplu LEFT JOIN user u on oplu.last_modified_by = u.id WHERE oplu.user_id = $opl_user_id");

    if($opl_qry->num_rows !== 0) {
      $opl = $opl_qry->fetch_assoc();

      echo $opl['name'];
    } else {
      echo 'Unknown';
    }

    break;
  case 'getOPLHistory':
    $history_array = array();

    $i = 1;

    $current_qry = $dbconn->query("SELECT JSON_LENGTH(opl) -2 AS oplLength, u.name AS lastModifiedBy FROM opl_users oplu LEFT JOIN user u on oplu.last_modified_by = u.id WHERE user_id = 42");
    $current = $current_qry->fetch_assoc();

    $history_array[0]['title'] = 'Current';
    $history_array[0]['oplCount'] = $current['oplLength'];
    $history_array[0]['updated_by'] = $current['lastModifiedBy'];
    $history_array[0]['id'] = 'live';

    $history_qry = $dbconn->query("SELECT oplh.id, JSON_LENGTH(opl) - 2 AS oplLength, timestamp, u.name AS lastModifiedBy FROM opl_history oplh LEFT JOIN user u on oplh.updated_by = u.id WHERE user_id = 42 ORDER BY timestamp DESC LIMIT 0, 20");

    if($history_qry->num_rows > 0) {
      while($history = $history_qry->fetch_assoc()) {
        $history_array[$i]['title'] = date(DATE_TIME_ABBRV, $history['timestamp']);
        $history_array[$i]['oplCount'] = $history['oplLength'];
        $history_array[$i]['updated_by'] = $history['lastModifiedBy'];
        $history_array[$i]['id'] = $history['id'];

        $i++;
      }
    }

    echo json_encode($history_array);

    break;
  case 'viewOPLHistorical':
    $id = sanitizeInput($_REQUEST['id']);
    $user_id = sanitizeInput($_REQUEST['user_id']);

    if($id !== 'live') {
      $flash_back_qry = $dbconn->query("SELECT opl FROM opl_history WHERE id = $id");

      if($flash_back_qry->num_rows === 1) {
        $flash_back = $flash_back_qry->fetch_assoc();

        echo $flash_back['opl'];
      }
    } else {
      $current_qry = $dbconn->query("SELECT opl FROM opl_users WHERE user_id = 42");
      $current = $current_qry->fetch_assoc();

      echo $current['opl'];
    }

    break;
  case 'saveOPLRowInfo':
    $unique_id = $_REQUEST['unique_id'];
    $note = $_REQUEST['note'];
    $user_id = $_REQUEST['user_id'];

    $insert = $dbconn->prepare('INSERT INTO opl_row_info (user_id, unique_id, note, timestamp) VALUES (?, ?, ?, UNIX_TIMESTAMP())');
    $insert->bind_param('iss', $_SESSION['userInfo']['id'], $unique_id, $note);

    if($insert->execute()) {
      echo displayToast('success', 'Successfully recorded notes.', 'Notes Recorded');
    } else {
      dbLogSQLErr($dbconn);
    }

    break;
}