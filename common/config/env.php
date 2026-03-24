<?php
/**
 * Setup application environment
 */
$dotenv = new \Dotenv\Dotenv(dirname(dirname(__DIR__)));
$dotenv->load();

defined('YII_DEBUG') or define('YII_DEBUG', ($_ENV['YII_DEBUG'] ?? 'false') === 'true');
defined('YII_ENV') or define('YII_ENV', $_ENV['YII_ENV'] ?? 'prod');

defined('PAYCOM_LIVE') or define('PAYCOM_LIVE', ($_ENV['PAYCOM_LIVE'] ?? 'false') == 'true');
defined('PAYNET_LIVE') or define('PAYNET_LIVE', ($_ENV['PAYNET_LIVE'] ?? 'false') == 'true');
defined('APELSIN_LIVE') or define('APELSIN_LIVE', ($_ENV['APELSIN_LIVE'] ?? 'false') == 'true');
defined('CLICK_LIVE') or define('CLICK_LIVE', ($_ENV['CLICK_LIVE'] ?? 'false') == 'true');
defined('AGR_LIVE') or define('AGR_LIVE', ($_ENV['AGR_LIVE'] ?? 'false') == 'true');
defined('UPAY_LIVE') or define('UPAY_LIVE', ($_ENV['UPAY_LIVE'] ?? 'false') == 'true');

defined('ASSET_BUNDLE') or define('ASSET_BUNDLE', ($_ENV['ASSET_BUNDLE'] ?? 'false') == 'true');