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

$route['api/location/gepee/divre']['get'] = 'location/gepee_divre';
$route['api/location/gepee/divre/(:any)/witel']['get'] = 'location/gepee_witel_by_divre/$1';
$route['api/location/gepee/witel/(:any)']['get'] = 'location/gepee_witel/$1';
$route['api/location/gepee/witel']['get'] = 'location/gepee_witel';
$route['api/location/divre']['get'] = 'location/divre';
$route['api/location/divre/(:any)/witel']['get'] = 'location/witel_by_divre/$1';
$route['api/location/witel/(:any)']['get'] = 'location/witel/$1';
$route['api/location/witel']['get'] = 'location/witel';

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
$route['api/monitoring/pue'] = 'monitoring/rtu_list_by_pue';
$route['api/monitoring/pue/(:any)'] = 'monitoring/pue/$1';
$route['api/monitoring/rtulist/(:any)/(:any)'] = 'monitoring/rtulist/$1/$2';
$route['api/monitoring/rtudetail/(:any)'] = 'monitoring/rtudetail/$1';

$route['api/rtu/(:any)']['get'] = 'rtu/index/$1';
$route['api/rtu/(:any)']['put'] = 'rtu/update/$1';
$route['api/rtu/(:any)']['delete'] = 'rtu/del/$1';
$route['api/rtu']['get'] = 'rtu';
$route['api/rtu']['post'] = 'rtu/add';

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
$route['api/activity/schedule']['get'] = 'activity_schedule';
$route['api/activity/schedule']['post'] = 'activity_schedule';

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
