<?php

$time_start = microtime(true); # Used by MTS().

/* Includes, constants, error_reporting() level, session_start(), OBBLM run requirements, MySQL connection, language load. */
require('header.php');

/********************
 *   Main routine
 ********************/

// Make 'main' the default section if no GET section request was sent.
if (!isset($_GET['section'])) {
    $_GET['section'] = 'main';
}

// Login?
$_VISSTATE['COOKIE'] = Coach::cookieLogin(); # If not already logged in then check for login-cookie and try to log in using the stored credentials.
if ($_VISSTATE['POST_IN'] = isset($_POST['login'])) {
    if (get_magic_quotes_gpc()) {
        $_POST['coach'] = stripslashes($_POST['coach']);
        $_POST['passwd'] = stripslashes($_POST['passwd']);
    }
    if (!Coach::login($_POST['coach'], $_POST['passwd'], isset($_POST['remember']))) {
        $_GET['section'] = 'login';
    }
}

// Mobile?
$isMobile = isset($_GET['mobile']) ? ($_GET['mobile'] == '1') : false;

// Logout?
if ($_VISSTATE['POST_OUT'] = isset($_GET['logout'])) {
    $_GET['section'] = 'main'; # Redirect logged out users to the main page.
    Coach::logout();
}

Mobile::setIsMobile($isMobile);

if ($isMobile && !Coach::isLoggedIn()) {
    // Redirect logged out mobile users to login
    $_GET['section'] = 'login';
}

if ($_VISSTATE['COOKIE'] || $_VISSTATE['POST_IN'] || $_VISSTATE['POST_OUT']) {
    setupGlobalVars(T_SETUP_GLOBAL_VARS__POST_COACH_LOGINOUT);
}

// Generate page
if(Mobile::isMobile()) {
	HTMLOUT::mobile_frame_begin(); # Make page frame, banner and menu.
	MTS('Header loaded, login auth, html frame generated');
	
	// Check if a menu-link was picked, and execute section code from sections.php accordingly.
	switch ($_GET['section'])
	{
		case 'login':       sec_login();                            break;
		case 'matches':     Match_HTMLOUT::userSched();             break;
		case 'management':	$teamId = Mobile_HTMLOUT::getSelectedTeamId();
							Team_HTMLOUT::teamManagementBox($teamId);
							break;
		default:            Mobile_HTMLOUT::sec_mobile_main();
	}
} else {
	HTMLOUT::frame_begin(); # Make page frame, banner and menu.
	MTS('Header loaded, login auth, html frame generated');
	
	// Check if a menu-link was picked, and execute section code from sections.php accordingly.
	switch ($_GET['section'])
	{
        case 'login':           sec_login();            break;
        case 'admin':           sec_admin();            break;
        case 'teamlist':        sec_teamlist();         break;
        case 'coachlist':       sec_coachlist();        break;
        case 'rules':           sec_rules();            break;
        case 'about':           sec_about();            break;
        case 'matches':         sec_matcheshandler();   break; // Tournaments, matches, match reports, recent matches, upcoming matches etc.
        case 'objhandler':      sec_objhandler();       break; // Object profiles, object standings.
        case 'requestleague':   sec_requestleague();    break;   
        case 'stars':   		sec_stars();    		break; // List of Star Players
        default:             	sec_main();
	}
}

HTMLOUT::frame_end(); // Spit out all the end-tags.
mysql_close($conn);
MTS('END OF SCRIPT');