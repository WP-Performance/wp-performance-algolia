<?php

namespace WPPerfomance\Algolia\Inc;


// singleton class
class SearchAlgoliaClient
{
    private static $instance = null;

    public static $app_id = null;

    public static $app_secret = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            if (!self::$app_id || !self::$app_secret) {
                throw new \Exception('PHP constant ALGOLIA_APP_ID and ALGOLIA_APP_PUBLIC are not set in wp-config.php file');
            }
            self::$instance = \Algolia\AlgoliaSearch\SearchClient::create(self::$app_id, self::$app_secret);
        }

        return self::$instance;
    }

    public static function initKeys($app_id, $app_secret)
    {
        self::$app_id = $app_id;
        self::$app_secret = $app_secret;
    }
}
