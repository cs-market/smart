<?php

use Tygh\Registry;
use Tygh\Tools\Url;
use Tygh\Enum\SiteArea;

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks('user_init', 'update_user_pre', 'update_product_post');

// fn_init_extended_api делает $this->authenticate(); поэтому надо после вызова $application['session']->init(), но до fn_init_user потому что там корзина и все дела
if (defined('API')) {
    $stack = Registry::get('init_stack');
    $after_fn_init_user = array_search(['fn_init_user'], $stack);
    array_splice($stack, $after_fn_init_user, 0, [['fn_init_extended_api']]);
    Registry::set('init_stack', $stack);
}

Tygh::$app->extend('session', function (&$session, $app) {
    $session = new \Tygh\Web\Session($app);

    // Configure conditions of session start
    if ((defined('NO_SESSION') && NO_SESSION && SiteArea::isAdmin(AREA)) || (defined('CONSOLE') && CONSOLE)) {
        $session->start_on_init = false;
        $session->start_on_read = false;
        $session->start_on_write = false;

        return $session;
    }

    $name_suffix = '_' . substr(md5(Registry::get('config.http_location')), 0, 5);

    // separate cookies for mobile app token for future?
    // if (defined('API') && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
    //     $name_suffix = '_' . substr($_SERVER['HTTP_AUTHORIZATION'], 6, 5);
    // }

    if (defined('HTTPS') && Registry::ifGet('config.tweaks.secure_cookies', false)) {
        $name_suffix = '_s' . $name_suffix;
        $session->cookie_secure = true;
    }

    // Configure session component
    $session->setSessionNamePrefix('sid_');
    $session->setSessionNameSuffix($name_suffix);
    $session->setName(ACCOUNT_TYPE);
    $session->setSessionIDSuffix('-' . AREA);

    $session->cache_limiter = 'nocache';
    $session->cookie_lifetime = SESSIONS_STORAGE_ALIVE_TIME;
    $session->cookie_path = Registry::ifGet('config.current_path', '/');

    $https_location = new Url(Registry::get('config.https_location'));
    $http_location = new Url(Registry::get('config.http_location'));

    // We shouldn't set secure subdomain as a cookie domain because it will cause
    // two SID cookies with the same name but different domains
    if (defined('HTTPS') && !$https_location->isSubDomainOf($http_location)) {
        $cookie_domain_host = $https_location->getHost();
    } else {
        $cookie_domain_host = $http_location->getHost();
    }

    if (($pos = strpos($cookie_domain_host, '.')) !== false) {
        $cookie_domain_host = $pos === 0 ? $cookie_domain_host : '.' . $cookie_domain_host;
    } else {
        // For local hosts set this to empty value
        $cookie_domain_host = '';
    }

    if (!preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $cookie_domain_host, $matches)) {
        $cookie_domain = $cookie_domain_host;
    } else {
        $cookie_domain = ini_get('session.cookie_domain');
    }

    $session->cookie_domain = $cookie_domain;

    $session->start_on_init = true;
    $session->start_on_read = false;
    $session->start_on_write = false;

    return $session;
});
