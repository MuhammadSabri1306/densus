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
$route['api/monitoring/pue_v2'] = 'monitoring/pue_v2';
$route['api/monitoring/pue/(:any)'] = 'monitoring/pue/$1';
$route['api/monitoring/pue'] = 'monitoring/rtu_list_by_pue';
$route['api/monitoring/rtulist/(:any)/(:any)'] = 'monitoring/rtulist/$1/$2';
$route['api/monitoring/rtudetail/(:any)'] = 'monitoring/rtudetail/$1';

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
$route['api/location/divre']['get'] = 'location/divre';
$route['api/location/divre/(:any)/witel']['get'] = 'location/witel_by_divre/$1';
$route['api/location/witel/(:any)']['get'] = 'location/witel/$1';
$route['api/location/witel']['get'] = 'location/witel';
$route['api/location/gepee']['post'] = 'location/gepee';

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

$route['api/activity/availablemonth']['get'] = 'activity_schedule/available_month';
$route['api/activity/schedule/(.*)']['get'] = 'activity_schedule';
$route['api/activity/schedule']['get'] = 'activity_schedule/index_v2';
$route['api/activity/schedule']['post'] = 'activity_schedule/index_v2';

$route['api/activity/execution/(:any)']['get'] = 'activity_execution/index/$1';
$route['api/activity/execution/(:any)']['post'] = 'activity_execution/index/$1';
$route['api/activity/execution/(:any)']['put'] = 'activity_execution/index/$1';
$route['api/activity/execution/(:any)']['delete'] = 'activity_execution/index/$1';
$route['api/activity/execution/(:any)/approve']['put'] = 'activity_execution/approve/$1';
$route['api/activity/execution/(:any)/reject']['put'] = 'activity_execution/reject/$1';

$route['api/user/(:any)']['get'] = 'user/index/$1';
$route['api/user/(:any)']['put'] = 'user/index/$1';
$route['api/user/(:any)/general']['put'] = 'user/update/$1';
$route['api/user/(:any)/active']['put'] = 'user/update_active/$1';
$route['api/user/(:any)']['delete'] = 'user/index/$1';
$route['api/user']['get'] = 'user';
$route['api/user']['post'] = 'user';
$route['api/profile']['get'] = 'user/profile';

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
