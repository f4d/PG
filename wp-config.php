<?php
# Database Configuration
define( 'DB_NAME', 'wp_petguardian' );
define( 'DB_USER', 'petguardian' );
define( 'DB_PASSWORD', 'O3UzHjwyorZLoV9a9pjT' );
define( 'DB_HOST', '127.0.0.1' );
define( 'DB_HOST_SLAVE', '127.0.0.1' );
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_unicode_ci');
$table_prefix = 'wp_';

# Security Salts, Keys, Etc
define('AUTH_KEY',         'I:h,g+)|ZZ{qR$Mv_]|nt6T+dxR}^#iSY@>w?}Z_0ga8Hs,GF.?LfHX8&Vt(C|+~');
define('SECURE_AUTH_KEY',  ';!h6g82@#g*&|$s^RI$,HbZyTGiB]8RI8yXTj2RJpy BGJL}qW5^^-b2a$<+(U v');
define('LOGGED_IN_KEY',    '4X=<tVB`NhT,SC%-?,{tSIS%MGA2|3oy26gB3k}Z6d+Kj,INIyt6~;_v_Ui-^p7R');
define('NONCE_KEY',        'AXM}++TUPrzr:4By%>BVyWrB,J bo|4So^^7F9o;7^(^4]{fH1=d9;k~K,LLi44/');
define('AUTH_SALT',        '-AtZ~<V=`)[T j*dwl]A.Kyo=E#-s8n|&/|xu6QBTp.^/L5bwY=M5@jALHU`RUAq');
define('SECURE_AUTH_SALT', ';*OLt3P-;E}*=;q!aKF:+T,|JO&s-r{ _d<|UWxEsX*:6/-~j;Fo.&-&K#|!5D3x');
define('LOGGED_IN_SALT',   '$.   2->+/qz^4<&b&KSIPQ8 LBSK:j|f2;}BZQ ;An%=n8@g-tj@q{Goe0<1g1z');
define('NONCE_SALT',       'UFS0vhUkZ$K a+1bb@3C[s}>m.4f01;xibBA(=uy&{tGz3taiTP<zcy*P<9I;His');


# Localized Language Stuff

define( 'WP_CACHE', TRUE );

define( 'WP_AUTO_UPDATE_CORE', false );

define( 'PWP_NAME', 'petguardian' );

define( 'FS_METHOD', 'direct' );

define( 'FS_CHMOD_DIR', 0775 );

define( 'FS_CHMOD_FILE', 0664 );

define( 'PWP_ROOT_DIR', '/nas/wp' );

define( 'WPE_APIKEY', '6d2daea5519e6e58de00d1d282c236d0bfd63641' );

define( 'WPE_FOOTER_HTML', "" );

define( 'WPE_CLUSTER_ID', '41561' );

define( 'WPE_CLUSTER_TYPE', 'pod' );

define( 'WPE_ISP', true );

define( 'WPE_BPOD', false );

define( 'WPE_RO_FILESYSTEM', false );

define( 'WPE_LARGEFS_BUCKET', 'largefs.wpengine' );

define( 'WPE_SFTP_PORT', 2222 );

define( 'WPE_LBMASTER_IP', '45.33.64.129' );

define( 'WPE_CDN_DISABLE_ALLOWED', true );

define( 'DISALLOW_FILE_EDIT', FALSE );

define( 'DISALLOW_FILE_MODS', FALSE );

define( 'DISABLE_WP_CRON', false );

define( 'WPE_FORCE_SSL_LOGIN', false );

define( 'FORCE_SSL_LOGIN', false );

/*SSLSTART*/ if ( isset($_SERVER['HTTP_X_WPE_SSL']) && $_SERVER['HTTP_X_WPE_SSL'] ) $_SERVER['HTTPS'] = 'on'; /*SSLEND*/

define( 'WPE_EXTERNAL_URL', false );

define( 'WP_POST_REVISIONS', FALSE );

define( 'WPE_WHITELABEL', 'wpengine' );

define( 'WP_TURN_OFF_ADMIN_BAR', false );

define( 'WPE_BETA_TESTER', false );

umask(0002);

$wpe_cdn_uris=array ( );

$wpe_no_cdn_uris=array ( );

$wpe_content_regexs=array ( );

$wpe_all_domains=array ( 0 => 'petguardian.wpengine.com', );

$wpe_varnish_servers=array ( 0 => 'pod-41561', );

$wpe_special_ips=array ( 0 => '45.33.64.129', );

$wpe_ec_servers=array ( );

$wpe_largefs=array ( );

$wpe_netdna_domains=array ( );

$wpe_netdna_domains_secure=array ( );

$wpe_netdna_push_domains=array ( );

$wpe_domain_mappings=array ( );

$memcached_servers=array ( );
define('WPLANG','');

# WP Engine ID


# WP Engine Settings






# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
require_once(ABSPATH . 'wp-settings.php');

$_wpe_preamble_path = null; if(false){}
