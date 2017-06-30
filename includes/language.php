<?php
/* Purpoase of file:
 * To define the variable language inside of the system
 */
// Release Date
define("RELEASE_DATE", "6/16/17");


// Global definitions
define("LOGO_TEXT", "Stone Mountain Cabinetry"); // The logo text displayed
define("TAB_TEXT", "SMCM - Dashboard"); // The text displayed in the title bar/tab
define("FOOTER_TEXT", "SMCM"); // Footer copyright name specifically
define("DATE_TIME_DEFAULT", "M jS Y @ g:i:s A T"); // the default date/time format to use
define("DATE_DEFAULT", "n/j/y"); // the default date format to use
define("DATE_TIME_ABBRV", "n/j/y @ g:i:s A"); // the abbreviated date/time format to use
define("TIME_ONLY", "g:i:s A"); // format for only time excluding date
define("SITE_URL", "http://smc.trustedprogrammer.com/"); // the SITE URL, used to configure header_start.php's includes

// Navigation bar definitions
define("NAV_DASHBOARD", "Dashboard"); // Navigation menu dashboard item name

/// Navigation > Shopfloor
define("NAV_SHOPFLOOR", "Shopfloor"); // Navigation menu Shopfloor
define("NAV_INDIVIDUAL", "Individual"); // Navigation menu Shopfloor Dashboard
define("NAV_WORKCENTER", "Workcenter"); // Navigation menu, create bracket
define("NAV_SHOP_LOGOUT", "Employee List"); // Navigation menu, log out
define("NAV_BRACKET_MGMT", "Bracket Management"); // Navigation menu, bracket management
//// Navigation > bracket management
define("NAV_BRACKET_NEW", "Create Bracket"); // Navigation menu, create bracket
define("NAV_BRACKET_LIST", "Existing Brackets"); // Navigation menu, existing bracket information

// Pricing bar definition
define("NAV_PRICINGPROGRAM", "Pricing System"); // Navigation menu Pricing Program

// Inventory bar definitions
define("NAV_INVENTORY", "Inventory"); // Navigation menu Inventory

// Accounting bar definitions
define("NAV_ACCOUNTING", "Accounting"); // Navigation menu Accounting

/// Navigation > Accounting
define("NAV_ACCOUNTING_TIMECARDS", "Timecard Report");

// Admin bar definitions
define("NAV_ADMIN", "Admin"); // Navigation menu Admin
define("NAV_KPI", "KPI"); // Navigation menu KPI
define("NAV_PBP", "PBP"); // Navigation menu Performance Based Pay
define("NAV_REPORTS", "Reports"); // Navigation menu Reports

// Navigation > CPanel
define("NAV_CPANEL", "CPanel"); // Control panel section
define("NAV_ADDUSER", "Add User"); // Control Panel -> Add User