<?php
/**
 * Include most things that's need to customize the site.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

/**
 * This file is not a valid entry point, perform no further processing unless
 * MEDIAWIKI is defined
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

# The main wiki script and things like database
# conversion and maintenance scripts all share a
# common setup of including lots of classes and
# setting up a few globals.
#

$fname = 'Setup.php';
wfProfileIn( $fname );
wfProfileIn( $fname  . '-defaults' );

// Check to see if we are at the file scope
if ( !isset( $wgVersion ) ) {
	echo "Error, Setup.php must be included from the file scope, after DefaultSettings.php\n";
	die( 1 );
}

// Set various default paths sensibly...
if ( $wgScript === false ) {
	$wgScript = "$wgScriptPath/index$wgScriptExtension";
}
if ( $wgLoadScript === false ) {
	$wgLoadScript = "$wgScriptPath/load$wgScriptExtension";
}

if ( $wgArticlePath === false ) {
	if ( $wgUsePathInfo ) {
		$wgArticlePath = "$wgScript/$1";
	} else {
		$wgArticlePath = "$wgScript?title=$1";
	}
}

if ( !empty( $wgActionPaths ) && !isset( $wgActionPaths['view'] ) ) {
	# 'view' is assumed the default action path everywhere in the code
	# but is rarely filled in $wgActionPaths
	$wgActionPaths['view'] = $wgArticlePath;
}

if ( $wgStylePath === false ) {
	$wgStylePath = "$wgScriptPath/skins";
}
if ( $wgLocalStylePath === false ) {
	$wgLocalStylePath = "$wgScriptPath/skins";
}
if ( $wgStyleDirectory === false ) {
	$wgStyleDirectory = "$IP/skins";
}
if ( $wgExtensionAssetsPath === false ) {
	$wgExtensionAssetsPath = "$wgScriptPath/extensions";
}

if ( $wgLogo === false ) {
	$wgLogo = "$wgStylePath/common/images/wiki.png";
}

if ( $wgUploadPath === false ) {
	$wgUploadPath = "$wgScriptPath/images";
}
if ( $wgUploadDirectory === false ) {
	$wgUploadDirectory = "$IP/images";
}
if ( $wgReadOnlyFile === false ) {
	$wgReadOnlyFile = "{$wgUploadDirectory}/lock_yBgMBwiR";
}
if ( $wgFileCacheDirectory === false ) {
	$wgFileCacheDirectory = "{$wgUploadDirectory}/cache";
}
if ( $wgDeletedDirectory === false ) {
	$wgDeletedDirectory = "{$wgUploadDirectory}/deleted";
}

if ( isset( $wgFileStore['deleted']['directory'] ) ) {
	$wgDeletedDirectory = $wgFileStore['deleted']['directory'];
}

if ( isset( $wgFooterIcons['copyright'] )
	&& isset( $wgFooterIcons['copyright']['copyright'] )
	&& $wgFooterIcons['copyright']['copyright'] === array()
) {
	if ( isset( $wgCopyrightIcon ) && $wgCopyrightIcon ) {
		$wgFooterIcons['copyright']['copyright'] = $wgCopyrightIcon;
	} elseif ( $wgRightsIcon || $wgRightsText ) {
		$wgFooterIcons['copyright']['copyright'] = array(
			'url' => $wgRightsUrl,
			'src' => $wgRightsIcon,
			'alt' => $wgRightsText,
		);
	} else {
		unset( $wgFooterIcons['copyright']['copyright'] );
	}
}

if ( isset( $wgFooterIcons['poweredby'] )
	&& isset( $wgFooterIcons['poweredby']['mediawiki'] )
	&& $wgFooterIcons['poweredby']['mediawiki']['src'] === null
) {
	$wgFooterIcons['poweredby']['mediawiki']['src'] = "$wgStylePath/common/images/poweredby_mediawiki_88x31.png";
}

/**
 * Unconditional protection for NS_MEDIAWIKI since otherwise it's too easy for a
 * sysadmin to set $wgNamespaceProtection incorrectly and leave the wiki insecure.
 *
 * Note that this is the definition of editinterface and it can be granted to
 * all users if desired.
 */
$wgNamespaceProtection[NS_MEDIAWIKI] = 'editinterface';

/**
 * The canonical names of namespaces 6 and 7 are, as of v1.14, "File"
 * and "File_talk".  The old names "Image" and "Image_talk" are
 * retained as aliases for backwards compatibility.
 */
$wgNamespaceAliases['Image'] = NS_FILE;
$wgNamespaceAliases['Image_talk'] = NS_FILE_TALK;

/**
 * Initialise $wgLockManagers to include basic FS version
 */
$wgLockManagers[] = array(
	'name' => 'fsLockManager',
	'class' => 'FSLockManager',
	'lockDirectory' => "{$wgUploadDirectory}/lockdir",
);
$wgLockManagers[] = array(
	'name' => 'nullLockManager',
	'class' => 'NullLockManager',
);

/**
 * Initialise $wgLocalFileRepo from backwards-compatible settings
 */
if ( !$wgLocalFileRepo ) {
	if ( isset( $wgFileStore['deleted']['hash'] ) ) {
		$deletedHashLevel = $wgFileStore['deleted']['hash'];
	} else {
		$deletedHashLevel = $wgHashedUploadDirectory ? 3 : 0;
	}
	$wgLocalFileRepo = array(
		'class' => 'LocalRepo',
		'name' => 'local',
		'directory' => $wgUploadDirectory,
		'scriptDirUrl' => $wgScriptPath,
		'scriptExtension' => $wgScriptExtension,
		'url' => $wgUploadBaseUrl ? $wgUploadBaseUrl . $wgUploadPath : $wgUploadPath,
		'hashLevels' => $wgHashedUploadDirectory ? 2 : 0,
		'thumbScriptUrl' => $wgThumbnailScriptPath,
		'transformVia404' => !$wgGenerateThumbnailOnParse,
		'deletedDir' => $wgDeletedDirectory,
		'deletedHashLevels' => $deletedHashLevel
	);
}
/**
 * Initialise shared repo from backwards-compatible settings
 */
if ( $wgUseSharedUploads ) {
	if ( $wgSharedUploadDBname ) {
		$wgForeignFileRepos[] = array(
			'class' => 'ForeignDBRepo',
			'name' => 'shared',
			'directory' => $wgSharedUploadDirectory,
			'url' => $wgSharedUploadPath,
			'hashLevels' => $wgHashedSharedUploadDirectory ? 2 : 0,
			'thumbScriptUrl' => $wgSharedThumbnailScriptPath,
			'transformVia404' => !$wgGenerateThumbnailOnParse,
			'dbType' => $wgDBtype,
			'dbServer' => $wgDBserver,
			'dbUser' => $wgDBuser,
			'dbPassword' => $wgDBpassword,
			'dbName' => $wgSharedUploadDBname,
			'dbFlags' => ( $wgDebugDumpSql ? DBO_DEBUG : 0 ) | DBO_DEFAULT,
			'tablePrefix' => $wgSharedUploadDBprefix,
			'hasSharedCache' => $wgCacheSharedUploads,
			'descBaseUrl' => $wgRepositoryBaseUrl,
			'fetchDescription' => $wgFetchCommonsDescriptions,
		);
	} else {
		$wgForeignFileRepos[] = array(
			'class' => 'FileRepo',
			'name' => 'shared',
			'directory' => $wgSharedUploadDirectory,
			'url' => $wgSharedUploadPath,
			'hashLevels' => $wgHashedSharedUploadDirectory ? 2 : 0,
			'thumbScriptUrl' => $wgSharedThumbnailScriptPath,
			'transformVia404' => !$wgGenerateThumbnailOnParse,
			'descBaseUrl' => $wgRepositoryBaseUrl,
			'fetchDescription' => $wgFetchCommonsDescriptions,
		);
	}
}
if ( $wgUseInstantCommons ) {
	$wgForeignFileRepos[] = array(
		'class' => 'ForeignAPIRepo',
		'name' => 'wikimediacommons',
		'apibase' => WebRequest::detectProtocol() === 'https' ?
			'https://commons.wikimedia.org/w/api.php' :
			'http://commons.wikimedia.org/w/api.php',
		'hashLevels' => 2,
		'fetchDescription' => true,
		'descriptionCacheExpiry' => 43200,
		'apiThumbCacheExpiry' => 86400,
	);
}
/*
 * Add on default file backend config for file repos.
 * FileBackendGroup will handle initializing the backends.
 */
if ( !isset( $wgLocalFileRepo['backend'] ) ) {
	$wgLocalFileRepo['backend'] = $wgLocalFileRepo['name'] . '-backend';
}
foreach ( $wgForeignFileRepos as &$repo ) {
	if ( !isset( $repo['directory'] ) && $repo['class'] === 'ForeignAPIRepo' ) {
		$repo['directory'] = $wgUploadDirectory; // b/c
	}
	if ( !isset( $repo['backend'] ) ) {
		$repo['backend'] = $repo['name'] . '-backend';
	}
}
unset( $repo ); // no global pollution; destroy reference

if ( $wgRCFilterByAge ) {
	# # Trim down $wgRCLinkDays so that it only lists links which are valid
	# # as determined by $wgRCMaxAge.
	# # Note that we allow 1 link higher than the max for things like 56 days but a 60 day link.
	sort( $wgRCLinkDays );
	for ( $i = 0; $i < count( $wgRCLinkDays ); $i++ ) {
		if ( $wgRCLinkDays[$i] >= $wgRCMaxAge / ( 3600 * 24 ) ) {
			$wgRCLinkDays = array_slice( $wgRCLinkDays, 0, $i + 1, false );
			break;
		}
	}
}

if ( $wgSkipSkin ) {
	$wgSkipSkins[] = $wgSkipSkin;
}

# Set default shared prefix
if ( $wgSharedPrefix === false ) {
	$wgSharedPrefix = $wgDBprefix;
}

if ( !$wgCookiePrefix ) {
	if ( $wgSharedDB && $wgSharedPrefix && in_array( 'user', $wgSharedTables ) ) {
		$wgCookiePrefix = $wgSharedDB . '_' . $wgSharedPrefix;
	} elseif ( $wgSharedDB && in_array( 'user', $wgSharedTables ) ) {
		$wgCookiePrefix = $wgSharedDB;
	} elseif ( $wgDBprefix ) {
		$wgCookiePrefix = $wgDBname . '_' . $wgDBprefix;
	} else {
		$wgCookiePrefix = $wgDBname;
	}
}
$wgCookiePrefix = strtr( $wgCookiePrefix, '=,; +."\'\\[', '__________' );

$wgUseEnotif = $wgEnotifUserTalk || $wgEnotifWatchlist;

if ( $wgMetaNamespace === false ) {
	$wgMetaNamespace = str_replace( ' ', '_', $wgSitename );
}

// Default value is either the suhosin limit or -1 for unlimited
if ( $wgResourceLoaderMaxQueryLength === false ) {
	$maxValueLength = ini_get( 'suhosin.get.max_value_length' );
	$wgResourceLoaderMaxQueryLength = $maxValueLength > 0 ? $maxValueLength : -1;
}

/**
 * Definitions of the NS_ constants are in Defines.php
 * @private
 */
$wgCanonicalNamespaceNames = array(
	NS_MEDIA            => 'Media',
	NS_SPECIAL          => 'Special',
	NS_TALK             => 'Talk',
	NS_USER             => 'User',
	NS_USER_TALK        => 'User_talk',
	NS_PROJECT          => 'Project',
	NS_PROJECT_TALK     => 'Project_talk',
	NS_FILE             => 'File',
	NS_FILE_TALK        => 'File_talk',
	NS_MEDIAWIKI        => 'MediaWiki',
	NS_MEDIAWIKI_TALK   => 'MediaWiki_talk',
	NS_TEMPLATE         => 'Template',
	NS_TEMPLATE_TALK    => 'Template_talk',
	NS_HELP             => 'Help',
	NS_HELP_TALK        => 'Help_talk',
	NS_CATEGORY         => 'Category',
	NS_CATEGORY_TALK    => 'Category_talk',
);

/// @todo UGLY UGLY
if ( is_array( $wgExtraNamespaces ) ) {
	$wgCanonicalNamespaceNames = $wgCanonicalNamespaceNames + $wgExtraNamespaces;
}

# These are now the same, always
# To determine the user language, use $wgLang->getCode()
$wgContLanguageCode = $wgLanguageCode;

# Easy to forget to falsify $wgShowIPinHeader for static caches.
# If file cache or squid cache is on, just disable this (DWIMD).
# Do the same for $wgDebugToolbar.
if ( $wgUseFileCache || $wgUseSquid ) {
	$wgShowIPinHeader = false;
	$wgDebugToolbar = false;
}

# Doesn't make sense to have if disabled.
if ( !$wgEnotifMinorEdits ) {
	$wgHiddenPrefs[] = 'enotifminoredits';
}

# $wgDisabledActions is deprecated as of 1.18
foreach ( $wgDisabledActions as $action ) {
	$wgActions[$action] = false;
}

# We always output HTML5 since 1.22, overriding these is no longer supported
# we set them here for extensions that depend on its value.
$wgHtml5 = true;
$wgXhtmlDefaultNamespace = 'http://www.w3.org/1999/xhtml';
$wgJsMimeType = 'text/javascript';

if ( !$wgHtml5Version && $wgAllowRdfaAttributes ) {
	# see http://www.w3.org/TR/rdfa-in-html/#document-conformance
	if ( $wgMimeType == 'application/xhtml+xml' ) {
		$wgHtml5Version = 'XHTML+RDFa 1.0';
	} else {
		$wgHtml5Version = 'HTML+RDFa 1.0';
	}
}

# Blacklisted file extensions shouldn't appear on the "allowed" list
$wgFileExtensions = array_values( array_diff ( $wgFileExtensions, $wgFileBlacklist ) );

if ( $wgArticleCountMethod === null ) {
	$wgArticleCountMethod = $wgUseCommaCount ? 'comma' : 'link';
}

if ( $wgInvalidateCacheOnLocalSettingsChange ) {
	$wgCacheEpoch = max( $wgCacheEpoch, gmdate( 'YmdHis', @filemtime( "$IP/LocalSettings.php" ) ) );
}

if ( $wgNewUserLog ) {
	# Add a new log type
	$wgLogTypes[] = 'newusers';
	$wgLogNames['newusers'] = 'newuserlogpage';
	$wgLogHeaders['newusers'] = 'newuserlogpagetext';
	$wgLogActionsHandlers['newusers/newusers'] = 'NewUsersLogFormatter';
	$wgLogActionsHandlers['newusers/create'] = 'NewUsersLogFormatter';
	$wgLogActionsHandlers['newusers/create2'] = 'NewUsersLogFormatter';
	$wgLogActionsHandlers['newusers/byemail'] = 'NewUsersLogFormatter';
	$wgLogActionsHandlers['newusers/autocreate'] = 'NewUsersLogFormatter';
}

if ( $wgCookieSecure === 'detect' ) {
	$wgCookieSecure = ( WebRequest::detectProtocol() === 'https' );
}

if ( $wgRC2UDPAddress ) {
	$wgRCFeeds['default'] = array(
		'formatter' => 'IRCColourfulRCFeedFormatter',
		'uri' => "udp://$wgRC2UDPAddress:$wgRC2UDPPort/$wgRC2UDPPrefix",
		'add_interwiki_prefix' => &$wgRC2UDPInterwikiPrefix,
		'omit_bots' => &$wgRC2UDPOmitBots,
	);
}

wfProfileOut( $fname  . '-defaults' );

// Disable MWDebug for command line mode, this prevents MWDebug from eating up
// all the memory from logging SQL queries on maintenance scripts
global $wgCommandLineMode;
if ( $wgDebugToolbar && !$wgCommandLineMode ) {
	wfProfileIn( $fname . '-debugtoolbar' );
	MWDebug::init();
	wfProfileOut( $fname . '-debugtoolbar' );
}

if ( !class_exists( 'AutoLoader' ) ) {
	require_once "$IP/includes/AutoLoader.php";
}

wfProfileIn( $fname . '-exception' );
MWExceptionHandler::installHandler();
wfProfileOut( $fname . '-exception' );

wfProfileIn( $fname . '-includes' );
require_once "$IP/includes/GlobalFunctions.php";
require_once "$IP/includes/normal/UtfNormalUtil.php";
require_once "$IP/includes/normal/UtfNormalDefines.php";
wfProfileOut( $fname . '-includes' );

wfProfileIn( $fname . '-defaults2' );
if ( $wgSecureLogin && substr( $wgServer, 0, 2 ) !== '//' ) {
	$wgSecureLogin = false;
	wfWarn( 'Secure login was enabled on a server that only supports HTTP or HTTPS. Disabling secure login.' );
}

# Now that GlobalFunctions is loaded, set defaults that depend
# on it.
if ( $wgTmpDirectory === false ) {
	wfProfileIn( $fname . '-tempDir' );
	$wgTmpDirectory = wfTempDir();
	wfProfileOut( $fname . '-tempDir' );
}

if ( $wgCanonicalServer === false ) {
	$wgCanonicalServer = wfExpandUrl( $wgServer, PROTO_HTTP );
}

// $wgHTCPMulticastRouting got renamed to $wgHTCPRouting in MediaWiki 1.22
// ensure back compatibility.
if ( !$wgHTCPRouting && $wgHTCPMulticastRouting ) {
	$wgHTCPRouting = $wgHTCPMulticastRouting;
}

// Initialize $wgHTCPRouting from backwards-compatible settings that
// comes from pre 1.20 version.
if ( !$wgHTCPRouting && $wgHTCPMulticastAddress ) {
	$wgHTCPRouting = array(
		'' => array(
			'host' => $wgHTCPMulticastAddress,
			'port' => $wgHTCPPort,
		)
	);
}

$wgDeferredUpdateList = array(); // b/c

wfProfileOut( $fname . '-defaults2' );
wfProfileIn( $fname . '-misc1' );

# Raise the memory limit if it's too low
wfMemoryLimit();

/**
 * Set up the timezone, suppressing the pseudo-security warning in PHP 5.1+
 * that happens whenever you use a date function without the timezone being
 * explicitly set. Inspired by phpMyAdmin's treatment of the problem.
 */
if ( is_null( $wgLocaltimezone ) ) {
	wfSuppressWarnings();
	$wgLocaltimezone = date_default_timezone_get();
	wfRestoreWarnings();
}

date_default_timezone_set( $wgLocaltimezone );
if ( is_null( $wgLocalTZoffset ) ) {
	$wgLocalTZoffset = date( 'Z' ) / 60;
}

# Useful debug output
if ( $wgCommandLineMode ) {
	$wgRequest = new FauxRequest( array() );

	wfDebug( "\n\nStart command line script $self\n" );
} else {
	# Can't stub this one, it sets up $_GET and $_REQUEST in its constructor
	$wgRequest = new WebRequest;

	$debug = "\n\nStart request {$wgRequest->getMethod()} {$wgRequest->getRequestURL()}\n";

	if ( $wgDebugPrintHttpHeaders ) {
		$debug .= "HTTP HEADERS:\n";

		foreach ( $wgRequest->getAllHeaders() as $name => $value ) {
			$debug .= "$name: $value\n";
		}
	}
	wfDebug( $debug );
}

wfProfileOut( $fname . '-misc1' );
if ( !defined( 'MW_SETUP_NO_CACHE' ) ) {
	wfProfileIn( $fname . '-memcached' );

	$wgMemc = wfGetMainCache();
	$messageMemc = wfGetMessageCacheStorage();
	$parserMemc = wfGetParserCacheStorage();
	$wgLangConvMemc = wfGetLangConverterCacheStorage();

	wfDebug( 'CACHES: ' . get_class( $wgMemc ) . '[main] ' .
		get_class( $messageMemc ) . '[message] ' .
		get_class( $parserMemc ) . "[parser]\n" );

	wfProfileOut( $fname . '-memcached' );
	# # Most of the config is out, some might want to run hooks here.
	wfRunHooks( 'SetupAfterCache' );
}

wfProfileIn( $fname . '-session' );

# If session.auto_start is there, we can't touch session name
if ( !wfIniGetBool( 'session.auto_start' ) ) {
	session_name( $wgSessionName ? $wgSessionName : $wgCookiePrefix . '_session' );
}

if ( !defined( 'MW_NO_SESSION' ) && !$wgCommandLineMode ) {
	if ( $wgRequest->checkSessionCookie() || isset( $_COOKIE[$wgCookiePrefix . 'Token'] ) ) {
		wfSetupSession();
		$wgSessionStarted = true;
	} else {
		$wgSessionStarted = false;
	}
}

wfProfileOut( $fname . '-session' );

if ( !defined( 'MW_SETUP_NO_CONTEXT' ) ) {
	wfProfileIn( $fname . '-globals' );

	$wgContLang = Language::factory( $wgLanguageCode );
	$wgContLang->initEncoding();
	$wgContLang->initContLang();

	// Now that variant lists may be available...
	$wgRequest->interpolateTitle();
	$wgUser = RequestContext::getMain()->getUser(); # BackCompat

	/**
	 * @var $wgLang Language
	 */
	$wgLang = new StubUserLang;

	/**
	 * @var OutputPage
	 */
	$wgOut = RequestContext::getMain()->getOutput(); # BackCompat

	/**
	 * @var $wgParser Parser
	 */
	$wgParser = new StubObject( 'wgParser', $wgParserConf['class'], array( $wgParserConf ) );

	if ( !is_object( $wgAuth ) ) {
		$wgAuth = new StubObject( 'wgAuth', 'AuthPlugin' );
		wfRunHooks( 'AuthPluginSetup', array( &$wgAuth ) );
	}

	# Placeholders in case of DB error
	$wgTitle = null;

	wfProfileOut( $fname . '-globals' );
}

wfProfileIn( $fname . '-extensions' );

# Extension setup functions for extensions other than skins
# Entries should be added to this variable during the inclusion
# of the extension file. This allows the extension to perform
# any necessary initialisation in the fully initialised environment
foreach ( $wgExtensionFunctions as $func ) {
	# Allow closures in PHP 5.3+
	if ( is_object( $func ) && $func instanceof Closure ) {
		$profName = $fname . '-extensions-closure';
	} elseif ( is_array( $func ) ) {
		if ( is_object( $func[0] ) ) {
			$profName = $fname . '-extensions-' . get_class( $func[0] ) . '::' . $func[1];
		} else {
			$profName = $fname . '-extensions-' . implode( '::', $func );
		}
	} else {
		$profName = $fname . '-extensions-' . strval( $func );
	}

	wfProfileIn( $profName );
	call_user_func( $func );
	wfProfileOut( $profName );
}

wfDebug( "Fully initialised\n" );
$wgFullyInitialised = true;

wfProfileOut( $fname . '-extensions' );
wfProfileOut( $fname );
