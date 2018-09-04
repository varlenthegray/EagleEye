<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

switch($_REQUEST['action']) {
  case 'getCompanyList':
    $company_out['data'] = [];

    if($company_sql = $dbconn->query('SELECT id, name, phone_number, annual_revenue, industry, created FROM crm_company')) {
      while($company = $company_sql->fetch_assoc()) {
        $company['last_contact'] = 'Never';
        $company['created'] = date(DATE_TIME_ABBRV, $company['created']);
        $company['annual_revenue'] = '$' . number_format( $company['annual_revenue']) . '.00';

        $company_out['data'][] = $company;
      }

      echo json_encode($company_out);
    } else {
      echo 'No records found.';
    }

    break;
}