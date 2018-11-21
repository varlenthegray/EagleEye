<?php
/* Purpoase of file:
 * To define the variable language inside of the system
 */
// Release Date
define('RELEASE_DATE', '11/21/18');
define('VERSION', '2.5.124');

// Global definitions
define('LOGO_TEXT', 'EagleEye ERP'); // The logo text displayed
define('TAB_TEXT', 'EagleEye ERP - The Shop Management Software'); // The text displayed in the title bar/tab
define('FOOTER_TEXT', 'EagleEye'); // Footer copyright name specifically
define('DATE_TIME_DEFAULT', 'M jS Y g:i:s A T'); // the default date/time format to use
define('DATE_DEFAULT', 'n/j/y'); // the default date format to use
define('DATE_TIME_ABBRV', 'n/j/y g:i:s A'); // the abbreviated date/time format to use
define('TIME_ONLY', 'g:i:s A'); // format for only time excluding date
define('SITE_URL', 'http://smc.trustedprogrammer.com/'); // the SITE URL, used to configure header_start.php's includes

// Navigation bar definitions
define('NAV_DASHBOARD', 'Dashboard'); // Navigation menu dashboard item name

/// Navigation > Shopfloor
define('NAV_SHOPFLOOR', 'Shopfloor'); // Navigation menu Shopfloor
define('NAV_INDIVIDUAL', 'Individual'); // Navigation menu Shopfloor Dashboard
define('NAV_WORKCENTER', 'Workcenter'); // Navigation menu, create bracket
define('NAV_SOLIST', 'SO List'); // Navigation menu SO List
define('NAV_SHOP_LOGOUT', 'Employee List'); // Navigation menu, log out
define('NAV_BRACKET_MGMT', 'Bracket Management'); // Navigation menu, bracket management
//// Navigation > bracket management
define('NAV_BRACKET_NEW', 'Create Bracket'); // Navigation menu, create bracket
define('NAV_BRACKET_LIST', 'Existing Brackets'); // Navigation menu, existing bracket information

// Inventory bar definitions
define('NAV_INVENTORY', 'Inventory'); // Navigation menu Inventory

// Accounting bar definitions
define('NAV_ACCOUNTING', 'Accounting'); // Navigation menu Accounting

/// Navigation > Accounting
define('NAV_ACCOUNTING_TIMECARDS', 'Timecards');

// Admin bar definitions
define('NAV_ADMIN', 'Admin'); // Navigation menu Admin
define('NAV_KPI', 'KPI'); // Navigation menu KPI
define('NAV_PBP', 'PBP'); // Navigation menu Performance Based Pay
define('NAV_REPORTS', 'Reports'); // Navigation menu Reports
define('NAV_NEW', 'New'); // Navigation menu New

// Navigation > CPanel
define('NAV_CPANEL', 'CPanel'); // Control panel section
define('NAV_ADDUSER', 'Add User'); // Control Panel -> Add User

define('NAV_JOBMANAGEMENT', 'Job Management'); // Job Management page
define('NAV_EMPLOYEELOGIN', 'Employees'); // List of employees and ability to impersonate
define('NAV_EMP_OPS', 'Employee Ops'); // List of employees and ability to impersonate
define('NAV_TASKS', 'Tasks'); // Task list
define('NAV_VIN', 'VIN'); // VIN management page
define('NAV_ADD_SO', 'Add SO'); // Adding a SO
define('NAV_FEEDBACK', 'Feedback'); // Providing feedback
define('NAV_LOGOUT', 'Logout'); // Logging out completely
define('NAV_CLOCKOUT', 'Clock Out'); // Clocking out early

define('NAV_QUICKADD', 'Quick Add'); // Quick add button

define('NAV_SALES_LIST', 'Sales List'); // Sales List Button

// default file types accepted (HTML)
define('FILE_TYPES', '.pdf,.jpg,.jpeg,.png,.bmp,.doc,.xls,.xlsx,.dwg,.vsdx,.docx,.kit,.cvj');

define('DEALER', strtoupper($_SESSION['userInfo']['username']));