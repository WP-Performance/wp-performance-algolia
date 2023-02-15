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
                throw new \Exception('Algolia app_id and app_secret are not set');
            }
            self::$instance = \Algolia\AlgoliaSearch\SearchClient::create(ALGOLIA_APP_ID, ALGOLIA_APP_SECRET);
        }

        return self::$instance;
    }

    public static function initKeys($app_id, $app_secret)
    {
        self::$app_id = $app_id;
        self::$app_secret = $app_secret;
    }
}
