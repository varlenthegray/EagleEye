<?php
require '../../../includes/header_start.php';

header('Content-Description: File Transfer');
header('Content-Type: text/plain');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

//outputPHPErrs();

$vin_schema = getVINSchema();

function getCVValue($segment, $row_result) {
  global $vin_schema;

  switch($segment) {
    case 'finish_code':
      $cstm_field = 'custom_finish_name';
      break;
  }

  if(false !== stripos($row_result[$segment], 'x')) {
    $custom_vinfo = json_decode($row_result['custom_vin_info'], true);

    if(array_key_exists($segment, $custom_vinfo)) {
      if(!empty(trim($custom_vinfo[$segment][$cstm_field]))) {
        return $custom_vinfo[$segment][$cstm_field];
      } else {
        return 'ERROR: EMPTY CUSTOM FINISH';
      }
    } else {
      return 'ERROR: FAILED FINISH';
    }
  } else {
    foreach($vin_schema[$segment] AS $row) {
      if($row['key'] === $row_result[$segment]) {
        return $row['cv_value'];
        break;
      }
    }
  }
}

$room_id = sanitizeInput($_REQUEST['roomID']);

$info_qry = $dbconn->query("SELECT * FROM rooms r 
  LEFT JOIN  sales_order so ON so.so_num = r.so_parent 
  LEFT JOIN dealers d ON so.dealer_code = d.dealer_id
WHERE r.id = $room_id");

if($info_qry->num_rows === 1) {
  $info = $info_qry->fetch_assoc();

  $_product_type = getCVValue('product_type', $info);
  $_species = getCVValue('species_grade', $info);
  $_finish = getCVValue('finish_code', $info);
  $_Description = "$_product_type/$_species/$_finish";

  $_Name = "{$info['so_num']}{$info['room']}_{$info['iteration']}";
  $_PurchaseOrder = "{$info['project_name']} {$info['room_name']}";
  $_Customer = $info['dealer_name'];
  $_CabinetConstruction = getCVValue('product_type', $info);

  header("Content-Disposition: attachment; filename=$_Name.ord");

  $_maple_keys = ['Ms', 'Mups', 'Mv', 'Pm'];

  if($info['construction_method'] === 'PBI') {
    $_material_addon = ' (Import)';
  } else {
    if(in_array($info['species_grade'], $_maple_keys, true)) {
      $_material_addon = ' (MUV2)';
    } else {
      $_material_addon = null;
    }
  }

  // if this is green gard or enviro-finish and a frameless cabinet and maple
  if($info['green_gard'] === 'G1' && $info['product_type'] === 'C' && ($info['species_grade'] === 'Pm' || $info['species_grade'] === 'Mv' || $info['species_grade'] === 'Mups')) {
    $_species = 'Maple Stain';
    $_material_addon = ' (MUV2)';
  }

  if($info['green_gard'] === 'G1' && $info['product_type'] === 'P' && ($info['species_grade'] === 'Pm' || $info['species_grade'] === 'Mv' || $info['species_grade'] === 'Mups')) {
    $_species = 'Maple Stain';
    $_material_addon = ' (MUV2)';
  }

  $_CabinetMaterials = "$_species{$_material_addon}";
  $_ExposedCabinetMaterials = "$_species FI{$_material_addon}";

  // now for the cabinet item list itself
  $cab_list_qry = $dbconn->query("SELECT * FROM pricing_cabinet_list WHERE room_id = $room_id");
  $cab_list = $cab_list_qry->fetch_assoc();


  $cab_list = json_decode($cab_list['cabinet_list'], true);

//  print_r($cab_list);

  $_cabinets = null;
  $i = 1; // the line number

  $_replaceThis = [',', '"'];
  $_replaceWith = [' -', ''];

  // build the actual cabinet list?
  foreach($cab_list AS $key => $cab_line) {
    $si = 1; // subline item number

    $_data = $cab_line['data'];

    if(!empty($_data['hinge'])) {
      $_hinge = $_data['hinge'];
    } else {
      $_hinge = '';
    }

    if(empty(trim($cab_line['title']))) {
      $_title = 'ZZ';
      $_comment = str_replace($_replaceThis, $_replaceWith, $_data['name']);
    } else {
      $_title = $cab_line['title'];
      $_comment = null;
    }

    $_comment = str_replace($_replaceThis, $_replaceWith, $_comment);

    // need to find out if there is finish end left or right inside of the cabinet itself
    $_fin_l = false;
    $_fin_r = false;
    $_finished_end = '*';

    if(!empty($cab_line['children'])) {
      foreach($cab_line['children'] AS $line) {
        if($line['title'] === 'F-L') {
          $_fin_l = true;
        }

        if($line['title'] === 'F-R') {
          $_fin_r = true;
        }

        if($_fin_l && $_fin_r) {
          $_finished_end = 'B';
        } elseif($_fin_l) {
          $_finished_end = 'L';
        } elseif($_fin_r) {
          $_finished_end = 'R';
        }
      }
    }

    $_cabinets .= "$i, \"$_title\", {$_data['width']}, {$_data['height']}, {$_data['depth']}, \"$_hinge\", \"$_finished_end\", {$_data['qty']}, \"$_comment\"\n  ";

    if(!empty($cab_line['children'])) {
      foreach($cab_line['children'] AS $subline) {
        $_slData = $subline['data'];

        $_slWidth = empty($_slData['width']) ? '' : $_slData['width'];
        $_slHeight = empty($_slData['height']) ? '' : $_slData['height'];
        $_slDepth = empty($_slData['depth']) ? '' : $_slData['depth'];

        if(empty(trim($subline['title']))) {
          $_slTitle = 'ZZ';
        } else {
          $_slTitle = $subline['title'];
        }

        $_slComment = str_replace($_replaceThis, $_replaceWith, $_slData['name']);

        $_cabinets.= "{$i}.{$si}, \"$_slTitle\", $_slWidth, $_slHeight, $_slDepth, \"$_hinge\", \"*\", {$_slData['qty']}, \"$_slComment\"\n  ";

        $si++;
      }
    }

    $i++;
  }

  $_itemList = <<<HEREDOC
  [Catalog]
  Name="1 - SMCM Frameless.cvc"
  
  [Cabinets]
  $_cabinets
HEREDOC;

  // final output
  echo <<<HEREDOC
  [Header]
  Version=4
  Unit=0
  Name="$_Name"
  Description="$_Description"
  PurchaseOrder="$_PurchaseOrder"
  Customer="$_Customer"
  ShipToCustomer="$_Customer"
  BaseDoors=""
  WallDoors=""
  DrawerFront=""
  BaseEndPanels=""
  WallEndPanels=""
  TallEndPanels=""
  CabinetConstruction="$_CabinetConstruction"
  DrawerBoxConstruction="5/8 Birch-Dovetail"
  RollOutConstruction="5/8 Birch-Dovetail RO"
  BaseCabinetMaterials="$_CabinetMaterials"
  WallCabinetMaterials="$_CabinetMaterials"
  BaseExposedCabinetMaterials="$_ExposedCabinetMaterials"
  WallExposedCabinetMaterials="$_ExposedCabinetMaterials"
  DrawerBoxMaterials="5/8 Birch"
  RollOutMaterials=" 5/8 Birch RO"
  PullMaterials=" SMCM"
  HingeMaterials="Frameless - 110"
  GuideMaterials="Blumotion Frameless"
  ClosetBaseDoors="Slab","Cherry - 3/4 - Standard","","","","","Door.ddb"
  ClosetWallDoors="Slab","Cherry - 3/4 - Standard","","","","","Door.ddb"
  ClosetDrawerFront="Slab","Cherry - 3/4 - Standard","","","","","Door.ddb"
  ClosetBaseEndPanels="Slab","Cherry - 3/4 - Standard","","","","","Door.ddb"
  ClosetWallEndPanels="Slab","Cherry - 3/4 - Standard","","","","","Door.ddb"
  ClosetTallEndPanels="Slab","Cherry - 3/4 - Standard","","","","","Door.ddb"
  ClosetCabinetConstruction="Sample Closet"
  ClosetDrawerBoxConstruction="System Drawer"
  ClosetRollOutConstruction="System Roll Out"
  ClosetMaterials="Maple Stain (MUV2)"
  ClosetDrawerBoxMaterials="19mm Tandem"
  ClosetRollOutMaterials=" 5/8 Birch RO"
  ClosetPullMaterials="None Pull"
  ClosetGuideMaterials="Blumotion Frameless"
  ClosetWireBasketMaterials="Sample"
  ClosetRodMaterials="Closet Rods"
  ClosetHingeMaterials="1/2 Overlay FF"
  InteriorFinish="System Bone"
  ExteriorFinish="System Bone"
  
$_itemList
HEREDOC;
}