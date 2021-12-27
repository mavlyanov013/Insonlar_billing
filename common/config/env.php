<?php
/**
 * Setup application environment
 */
$dotenv = new \Dotenv\Dotenv(dirname(dirname(__DIR__)));
$dotenv->load();


defined('YII_DEBUG') or define('YII_DEBUG', getenv('YII_DEBUG') === 'true' || isset($_GET['dbg']));
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV') ?: 'prod');
defined('PAYCOM_LIVE') or define('PAYCOM_LIVE', getenv('PAYCOM_LIVE') == 'true');
defined('PAYNET_LIVE') or define('PAYNET_LIVE', getenv('PAYNET_LIVE') == 'true');
defined('APELSIN_LIVE') or define('APELSIN_LIVE', getenv('APELSIN_LIVE') == 'true');
defined('KAPITAL_LIVE') or define('KAPITAL_LIVE', getenv('KAPITAL_LIVE') == 'true');
defined('CLICK_LIVE') or define('CLICK_LIVE', getenv('CLICK_LIVE') == 'true');
defined('PAYMO_LIVE') or define('PAYMO_LIVE', getenv('PAYMO_LIVE') == 'true');
defined('AGR_LIVE') or define('AGR_LIVE', getenv('AGR_LIVE') == 'true');
defined('UPAY_LIVE') or define('UPAY_LIVE', getenv('UPAY_LIVE') == 'true');
defined('ASSET_BUNDLE') or define('ASSET_BUNDLE', getenv('ASSET_BUNDLE') == 'true');
