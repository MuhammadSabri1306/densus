<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

// $route['api/login/(:any)/(:any)/(:any)']['get'] = 'auth/login/$1/$2/$3';
$route['api/login']['post'] = 'auth/login';
$route['api/logout']['get'] = 'auth/logout';
$route['api/change_password']['put'] = 'auth/update_password';

/* ENDPOINT MONITORING RTU */
$route['api/monitoring/costbbm/(:any)'] = 'monitoring/costbbm/$1';
$route['api/monitoring/performance/(:any)'] = 'monitoring/performance/$1';
$route['api/monitoring/kwhreal/(:any)'] = 'monitoring/kwhreal/$1';
$route['api/monitoring/savingpercent/(:any)'] = 'monitoring/savingpercent/$1';
$route['api/monitoring/kwhtotal/(:any)'] = 'monitoring/kwhtotal/$1';
$route['api/monitoring/kwhtoday/(:any)'] = 'monitoring/kwhtoday/$1';
$route['api/monitoring/totalalarm/(:any)'] = 'monitoring/totalalarm/$1';
$route['api/monitoring/tabledata/(:any)'] = 'monitoring/tabledata/$1';
$route['api/monitoring/degtabledata/(:any)'] = 'monitoring/degtabledata/$1';
$route['api/monitoring/chartdatadaily/(:any)'] = 'monitoring/chartdatadaily/$1';
$route['api/monitoring/divre'] = 'monitoring/divre';
$route['api/monitoring/witel/(:any)'] = 'monitoring/witel/$1';
$route['api/monitoring/location/(:any)'] = 'monitoring/location/$1';
$route['api/monitoring/pue_v2'] = 'monitoring/pue_v2'; // jgn lupa hapus
$route['api/monitoring/pue/(:any)'] = 'monitoring/pue/$1'; // jgn lupa hapus
$route['api/monitoring/pue'] = 'monitoring/rtu_list_by_pue'; // jgn lupa hapus
$route['api/monitoring/rtulist/(:any)/(:any)'] = 'monitoring/rtulist/$1/$2';
$route['api/monitoring/rtudetail/(:any)'] = 'monitoring/rtudetail/$1';

/* ENDPOINT MONITORING PUE */
// $route['api/pue/value_on_year/(:any)']['get'] = 'monitoring_pue/value_on_year/$1';
// $route['api/pue/avg_on_year']['get'] = 'monitoring_pue/avg_value_on_year';
$route['api/pue/chart_data']['get'] = 'monitoring_pue/chart_data'; // jadi satu endpoint
$route['api/pue/sto_value_on_year']['get'] = 'monitoring_pue/sto_value_on_year';
// $route['api/pue/latest_value/(:any)']['get'] = 'monitoring_pue/latest_value/$1';
// $route['api/pue/latest_avg_value']['get'] = 'monitoring_pue/latest_avg_value';
$route['api/pue/latest_value']['get'] = 'monitoring_pue/latest_value'; // jadi satu endpoint
$route['api/pue/avg_value']['get'] = 'monitoring_pue/avg_value';
$route['api/pue/max_value']['get'] = 'monitoring_pue/max_value';
$route['api/pue/performance']['get'] = 'monitoring_pue/performance';

/* ENDPOINT GEPEE EVIDENCE */
$route['api/gepee-evidence/location/info']['get'] = 'gepee_evidence/location_info';
$route['api/gepee-evidence/location']['get'] = 'gepee_evidence/location';
$route['api/gepee-evidence/category/(:any)']['get'] = 'gepee_evidence/category/$1';
$route['api/gepee-evidence/category']['get'] = 'gepee_evidence/category';
$route['api/gepee-evidence/category-data']['get'] = 'gepee_evidence/category_data';
$route['api/gepee-evidence/evidence/category/(:any)']['get'] = 'gepee_evidence/evidence_list/$1';
$route['api/gepee-evidence/evidence/(:any)']['get'] = 'gepee_evidence/evidence/$1';
$route['api/gepee-evidence/evidence']['post'] = 'gepee_evidence/evidence';
$route['api/gepee-evidence/evidence/(:any)']['put'] = 'gepee_evidence/evidence/$1';
$route['api/gepee-evidence/evidence/(:any)']['delete'] = 'gepee_evidence/evidence/$1';

/* ENDPOINT MONITORING ENERGY (PLN PALING BARU) */
$route['api/monitoring/pln/bill/location']['get'] = 'pln/bill_location';
$route['api/monitoring/pln/bill/location']['post'] = 'pln/bill_location';
$route['api/monitoring/pln/bill/location/(:any)']['put'] = 'pln/bill_location/$1';
$route['api/monitoring/pln/bill/location/(:any)']['delete'] = 'pln/bill_location/$1';
$route['api/monitoring/pln/bill']['get'] = 'pln/bill';
$route['api/monitoring/pln/bill']['post'] = 'pln/bill';
$route['api/monitoring/pln/bill/(:any)']['put'] = 'pln/bill/$1';
$route['api/monitoring/pln/bill/(:any)']['delete'] = 'pln/bill/$1';
$route['api/monitoring/pln/params']['get'] = 'pln/params';
$route['api/monitoring/pln/params']['post'] = 'pln/params';
$route['api/monitoring/pln/params/(:any)']['put'] = 'pln/params/$1';
$route['api/monitoring/pln/params/(:any)']['delete'] = 'pln/params/$1';
$route['api/monitoring/fuel/invoice/location']['get'] = 'fuel/invoice_location';
$route['api/monitoring/fuel/invoice/location']['post'] = 'fuel/invoice_location';
$route['api/monitoring/fuel/invoice/location/(:any)']['put'] = 'fuel/invoice_location/$1';
$route['api/monitoring/fuel/invoice/location/(:any)']['delete'] = 'fuel/invoice_location/$1';
$route['api/monitoring/fuel/invoice']['get'] = 'fuel/invoice';
$route['api/monitoring/fuel/invoice']['post'] = 'fuel/invoice';
$route['api/monitoring/fuel/invoice/(:any)']['put'] = 'fuel/invoice/$1';
$route['api/monitoring/fuel/invoice/(:any)']['delete'] = 'fuel/invoice/$1';
$route['api/monitoring/fuel/params']['get'] = 'fuel/params';
$route['api/monitoring/fuel/params']['post'] = 'fuel/params';
$route['api/monitoring/fuel/params/(:any)']['put'] = 'fuel/params/$1';
$route['api/monitoring/fuel/params/(:any)']['delete'] = 'fuel/params/$1';

/* ENDPOINT RTU MAP */
$route['api/rtu/(:any)']['get'] = 'rtu/index/$1';
$route['api/rtu/(:any)']['put'] = 'rtu/update/$1';
$route['api/rtu/(:any)']['delete'] = 'rtu/del/$1';
$route['api/rtu']['get'] = 'rtu';
$route['api/rtu']['post'] = 'rtu/add';

/* ENDPOINT MONITORING PLN YG LAMA */
$route['api/pln']['get'] = 'pln_billing';
$route['api/pln']['post'] = 'pln_billing';
$route['api/pln/(:any)']['put'] = 'pln_billing/index/$1';
$route['api/pln/(:any)']['delete'] = 'pln_billing/index/$1';

/* ENDPOINT MONITORING PLN YG BARU */
$route['api/pln/billing']['get'] = 'pln/billing';
$route['api/pln/billing']['post'] = 'pln/billing';
$route['api/pln/billing/(:any)']['put'] = 'pln/billing/$1';
$route['api/pln/billing/(:any)']['delete'] = 'pln/billing/$1';
$route['api/pln/params']['get'] = 'pln/monitoring_params';
$route['api/pln/params']['post'] = 'pln/monitoring_params';
$route['api/pln/params/(:any)']['put'] = 'pln/monitoring_params/$1';
$route['api/pln/params/(:any)']['delete'] = 'pln/monitoring_params/$1';

$route['api/fuel/invoice']['get'] = 'fuel/invoice';
$route['api/fuel/invoice']['post'] = 'fuel/invoice';
$route['api/fuel/invoice/(:any)']['put'] = 'fuel/invoice/$1';
$route['api/fuel/invoice/(:any)']['delete'] = 'fuel/invoice/$1';
// perlu update
$route['api/fuel/params']['get'] = 'fuel/monitoring_params';
$route['api/fuel/params']['post'] = 'fuel/monitoring_params';
$route['api/fuel/params/(:any)']['put'] = 'fuel/monitoring_params/$1';
$route['api/fuel/params/(:any)']['delete'] = 'fuel/monitoring_params/$1';

$route['api/location/gepee/divre']['get'] = 'location/gepee_divre';
$route['api/location/gepee/divre/(:any)/witel']['get'] = 'location/gepee_witel_by_divre/$1';
$route['api/location/gepee/witel/(:any)']['get'] = 'location/gepee_witel/$1';
$route['api/location/gepee/witel']['get'] = 'location/gepee_witel';
$route['api/location/gepee/sto/(:any)/(:any)']['get'] = 'location/gepee_sto/$1/$2';
$route['api/location/gepee/sto/(:any)']['get'] = 'location/gepee_sto/$1';
$route['api/location/gepee/sto']['get'] = 'location/gepee_sto';
$route['api/location/gepee']['post'] = 'location/gepee';

$route['api/location/sto-master/divre']['get'] = 'location/sto_master_divre';
$route['api/location/sto-master/divre/(:any)/witel']['get'] = 'location/sto_master_witel_by_divre/$1';
$route['api/location/sto-master/witel/(:any)']['get'] = 'location/sto_master_witel/$1';
$route['api/location/sto-master/witel']['get'] = 'location/sto_master_witel';
$route['api/location/sto-master/sto/(:any)/(:any)']['get'] = 'location/sto_master_sto/$1/$2';
$route['api/location/sto-master/sto/(:any)']['get'] = 'location/sto_master_sto/$1';
$route['api/location/sto-master/sto']['get'] = 'location/sto_master_sto';

$route['api/location/divre']['get'] = 'location/divre';
$route['api/location/divre/(:any)/witel']['get'] = 'location/witel_by_divre/$1';
$route['api/location/witel/(:any)']['get'] = 'location/witel/$1';
$route['api/location/witel']['get'] = 'location/witel';

$route['api/activity/divre']['get'] = 'activity/divre';
$route['api/activity/divre/(:any)/witel']['get'] = 'activity/witel_by_divre/$1';
$route['api/activity/witel/(:any)']['get'] = 'activity/witel/$1';
$route['api/activity/witel']['get'] = 'activity/witel';
$route['api/activity/lokasi/(:any)/(:any)']['get'] = 'activity/lokasi/$1/$2';
$route['api/activity/lokasi/(:any)']['get'] = 'activity/lokasi/$1';
$route['api/activity/lokasi']['get'] = 'activity/lokasi';
$route['api/activity/category']['get'] = 'activity/category';
$route['api/activity/dashboard']['get'] = 'activity/dashboard';
$route['api/activity/chart']['get'] = 'activity/chart';
$route['api/activity/performance']['get'] = 'activity/performance';

$route['api/activity/availablemonth']['get'] = 'activity_schedule/available_month';
$route['api/activity/schedule/(.*)']['get'] = 'activity_schedule/index_v2';
$route['api/activity/schedule']['get'] = 'activity_schedule/index_v2';
$route['api/activity/schedule-v2']['get'] = 'activity_schedule/index_v3';
$route['api/activity/schedule']['post'] = 'activity_schedule/index_v2';

$route['api/activity/execution/(:any)']['get'] = 'activity_execution/index/$1';
$route['api/activity/execution/(:any)']['post'] = 'activity_execution/index/$1';
$route['api/activity/execution/(:any)']['put'] = 'activity_execution/index/$1';
$route['api/activity/execution/(:any)']['delete'] = 'activity_execution/index/$1';
$route['api/activity/execution/(:any)/approve']['put'] = 'activity_execution/approve/$1';
$route['api/activity/execution/(:any)/reject']['put'] = 'activity_execution/reject/$1';

$route['api/pue/offline/location']['post'] = 'pue_offline/location';
$route['api/pue/offline/location/(:any)']['put'] = 'pue_offline/location/$1';
$route['api/pue/offline/location/(:any)']['delete'] = 'pue_offline/location/$1';
$route['api/pue/offline/location']['get'] = 'pue_offline/location_data';
$route['api/pue/offline/(:num)/(:num)']['get'] = 'pue_offline/index/$1/$2';
$route['api/pue/offline/(:num)']['get'] = 'pue_offline/index/$1';
$route['api/pue/offline']['post'] = 'pue_offline';
$route['api/pue/offline/(:num)']['put'] = 'pue_offline/index/$1';
$route['api/pue/offline/(:num)']['delete'] = 'pue_offline/index/$1';

$route['api/gepee-report']['get'] = 'gepee_report'; // hapus nanti
$route['api/gepee-report-v2']['get'] = 'gepee_report/index_v2';
$route['api/gepee-report/nasional']['get'] = 'gepee_report/nasional';

$route['api/pue-target/report']['get'] = 'pue_target/report_v2';
// $route['api/pue-target/report-v2']['get'] = 'pue_target/report_v2';
$route['api/pue-target/report/location-status']['get'] = 'pue_target/location_status';
$route['api/pue-target']['get'] = 'pue_target';
$route['api/pue-target']['post'] = 'pue_target';
$route['api/pue-target/(:num)']['put'] = 'pue_target/index/$1';
$route['api/pue-target/(:num)']['delete'] = 'pue_target/index/$1';

$route['api/oxisp/list/(:num)/(:num)/(:num)']['get'] = 'oxisp_activity/sto_month_data/$1/$2/$3';
$route['api/oxisp/list']['get'] = 'oxisp_activity/performance';
$route['api/oxisp/location']['get'] = 'oxisp_activity/location';
$route['api/oxisp/location']['post'] = 'oxisp_activity/location';
$route['api/oxisp/location/(:num)']['put'] = 'oxisp_activity/location/$1';
$route['api/oxisp/location/(:num)']['delete'] = 'oxisp_activity/location/$1';
$route['api/oxisp/approve/(:num)']['put'] = 'oxisp_activity/approve/$1';
$route['api/oxisp/reject/(:num)']['put'] = 'oxisp_activity/reject/$1';
$route['api/oxisp/(:num)/(:num)']['post'] = 'oxisp_activity/index/$1/$2';
$route['api/oxisp/(:num)']['put'] = 'oxisp_activity/index/$1';
$route['api/oxisp/(:num)']['delete'] = 'oxisp_activity/index/$1';

$route['api/user/(:any)']['get'] = 'user/index/$1';
$route['api/user/(:any)']['put'] = 'user/index/$1';
$route['api/user/(:any)/general']['put'] = 'user/update/$1';
$route['api/user/(:any)/active']['put'] = 'user/update_active/$1';
$route['api/user/(:any)']['delete'] = 'user/index/$1';
$route['api/user']['get'] = 'user';
$route['api/user']['post'] = 'user';
$route['api/profile']['get'] = 'user/profile';

/* ENDPOINT EXPORT DATA */
$route['export/excel/pue'] = 'excel_export/pue';
$route['export/excel/pue/rtu'] = 'excel_export/pue_rtu';
$route['export/excel/activity/performance'] = 'excel_export/activity_performance';
$route['export/excel/activity/schedule'] = 'excel_export/activity_schedule';
$route['export/excel/activity/execution'] = 'excel_export/activity_execution';
$route['export/excel/gepee-report'] = 'excel_export/gepee_report';
$route['export/excel/gepee-evidence'] = 'excel_export/gepee_evidence';
$route['export/excel/opnimus-sto'] = 'excel_export/get_opnimus_master_sto';

/* ENDPOINT FILE ATTACHMENT */
// $route['api/attachment/activity/check']['get'] = 'attachment/check_activity_execution';
$route['api/attachment/activity']['post'] = 'attachment/store_activity_execution';
$route['api/attachment/activity/(:any)']['delete'] = 'attachment/del_activity_execution/$1';
$route['api/attachment/gepee-evidence']['post'] = 'attachment/store_gepee_evidence';
$route['api/attachment/gepee-evidence/(:any)']['delete'] = 'attachment/del_gepee_evidence/$1';
$route['api/attachment/pue/offline']['post'] = 'attachment/store_pue_evidence';
$route['api/attachment/pue/offline/(:any)']['delete'] = 'attachment/del_pue_evidence/$1';
$route['api/attachment/oxisp']['post'] = 'attachment/store_oxisp_evidence';
$route['api/attachment/oxisp/(:any)']['delete'] = 'attachment/del_oxisp_evidence/$1';

$route['test/get_pue_chart_data'] = 'test/get_pue_chart_data';
$route['test/cron_store_pue_counter'] = 'test/cron_store_pue_counter';
$route['test/newosase-api'] = 'test/newosase_api';
$route['test/uploaded-list'] = 'test/list_uploaded_file';
$route['test/setup-db-opnimus'] = 'test/setup_db_opnimus_new';

$route['login'] = 'vue';
$route['monitoring/(:any)/(:any)'] = 'vue';
$route['monitoring/(:any)'] = 'vue';
$route['monitoring'] = 'vue';
$route['rtu/(:any)/(:any)'] = 'vue';
$route['rtu/(:any)'] = 'vue';
$route['rtu'] = 'vue';
$route['activity/(:any)/(:any)'] = 'vue';
$route['activity/(:any)'] = 'vue';
$route['activity'] = 'vue';
$route['user/(:any)'] = 'vue';
$route['user'] = 'vue';

// $route['dev/(:any)/(:any)'] = 'vue/dev/$1/$2';
// $route['updatedev'] = 'vue/updatedev';
$route['default_controller'] = 'vue';
// $route['(.*)'] = 'vue/index';
$route['404_override'] = 'vue';
$route['translate_uri_dashes'] = FALSE;