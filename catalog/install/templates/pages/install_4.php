<?php
use OSC\OM\Cache;
use OSC\OM\Db;
use OSC\OM\FileSystem;
use OSC\OM\Hash;
use OSC\OM\HTML;
use OSC\OM\Language;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

$OSCOM_Db = Db::initialize($_POST['DB_SERVER'], $_POST['DB_SERVER_USERNAME'], $_POST['DB_SERVER_PASSWORD'], $_POST['DB_DATABASE']);

Registry::set('Db', $OSCOM_Db);

$OSCOM_Db->setTablePrefix($_POST['DB_TABLE_PREFIX']);

$Qcfg = $OSCOM_Db->get('configuration', [
    'configuration_key as k',
    'configuration_value as v'
]);

while ($Qcfg->fetch()) {
    define($Qcfg->value('k'), $Qcfg->value('v'));
}

$OSCOM_Language = new Language();
Registry::set('Language', $OSCOM_Language);

$OSCOM_Db->save('configuration', ['configuration_value' => $_POST['CFG_STORE_NAME']], ['configuration_key' => 'STORE_NAME']);
$OSCOM_Db->save('configuration', ['configuration_value' => $_POST['CFG_STORE_OWNER_NAME']], ['configuration_key' => 'STORE_OWNER']);
$OSCOM_Db->save('configuration', ['configuration_value' => $_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']], ['configuration_key' => 'STORE_OWNER_EMAIL_ADDRESS']);

if (!empty($_POST['CFG_STORE_OWNER_NAME']) && !empty($_POST['CFG_STORE_OWNER_EMAIL_ADDRESS'])) {
    $OSCOM_Db->save('configuration', ['configuration_value' => '"' . trim($_POST['CFG_STORE_OWNER_NAME']) . '" <' . trim($_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']) . '>'], ['configuration_key' => 'EMAIL_FROM']);
} else {
    $OSCOM_Db->save('configuration', ['configuration_value' => $_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']], ['configuration_key' => 'EMAIL_FROM']);
}

if (!empty($_POST['CFG_ADMINISTRATOR_USERNAME'])) {
    $Qcheck = $OSCOM_Db->prepare('select user_name from :table_administrators where user_name = :user_name');
    $Qcheck->bindValue(':user_name', $_POST['CFG_ADMINISTRATOR_USERNAME']);
    $Qcheck->execute();

    if ($Qcheck->fetch() !== false) {
        $OSCOM_Db->save('administrators', ['user_password' => Hash::encrypt(trim($_POST['CFG_ADMINISTRATOR_PASSWORD']))], ['user_name' => $_POST['CFG_ADMINISTRATOR_USERNAME']]);
    } else {
        $OSCOM_Db->save('administrators', ['user_name' => $_POST['CFG_ADMINISTRATOR_USERNAME'], 'user_password' => Hash::encrypt(trim($_POST['CFG_ADMINISTRATOR_PASSWORD']))]);
    }
}

if (FileSystem::isWritable(OSCOM::BASE_DIR . 'Work')) {
    if (!is_dir(Cache::getPath())) {
        mkdir(Cache::getPath(), 0777);
    }

    if (!is_dir(OSCOM::BASE_DIR . 'Work/Session')) {
        mkdir(OSCOM::BASE_DIR . 'Work/Session', 0777);
    }
}

foreach (glob(Cache::getPath() . '*.cache') as $c) {
    unlink($c);
}

$dir_fs_document_root = $_POST['DIR_FS_DOCUMENT_ROOT'];

if ((substr($dir_fs_document_root, -1) != '\\') && (substr($dir_fs_document_root, -1) != '/')) {
    if (strrpos($dir_fs_document_root, '\\') !== false) {
        $dir_fs_document_root .= '\\';
    } else {
        $dir_fs_document_root .= '/';
    }
}

$http_url = parse_url($_POST['HTTP_WWW_ADDRESS']);
$http_server = $http_url['scheme'] . '://' . $http_url['host'];
$http_catalog = $http_url['path'];

if (isset($http_url['port']) && !empty($http_url['port'])) {
    $http_server .= ':' . $http_url['port'];
}

if (substr($http_catalog, -1) != '/') {
    $http_catalog .= '/';
}

$admin_folder = 'admin';

if (isset($_POST['CFG_ADMIN_DIRECTORY']) && !empty($_POST['CFG_ADMIN_DIRECTORY']) && FileSystem::isWritable($dir_fs_document_root) && FileSystem::isWritable($dir_fs_document_root . 'admin')) {
    $admin_folder = preg_replace('/[^a-zA-Z0-9]/', '', trim($_POST['CFG_ADMIN_DIRECTORY']));

    if (empty($admin_folder)) {
        $admin_folder = 'admin';
    }
}

if ($admin_folder != 'admin') {
    @rename($dir_fs_document_root . 'admin', $dir_fs_document_root . $admin_folder);
}

$dbServer = trim($_POST['DB_SERVER']);
$dbUsername = trim($_POST['DB_SERVER_USERNAME']);
$dbPassword = trim($_POST['DB_SERVER_PASSWORD']);
$dbDatabase = trim($_POST['DB_DATABASE']);
$dbTablePrefix = trim($_POST['DB_TABLE_PREFIX']);
$timezone = trim($_POST['TIME_ZONE']);

$file_contents = <<<ENDCFG
<?php
\$ini = <<<EOD
db_server = "{$dbServer}"
db_server_username = "{$dbUsername}"
db_server_password = "{$dbPassword}"
db_database = "{$dbDatabase}"
db_table_prefix = "{$dbTablePrefix}"
store_sessions = "MySQL"
time_zone = "{$timezone}"
EOD;

ENDCFG;
// last empty line needed

file_put_contents(OSCOM::BASE_DIR . 'Conf/global.php', $file_contents);

@chmod(OSCOM::BASE_DIR . 'Conf/global.php', 0644);

$file_contents = <<<ENDCFG
<?php
\$ini = <<<EOD
dir_root = "{$dir_fs_document_root}"
http_server = "{$http_server}"
http_path = "{$http_catalog}"
http_images_path = "images/"
http_cookie_domain = ""
http_cookie_path = "{$http_catalog}"
EOD;

ENDCFG;
// last empty line needed

file_put_contents(OSCOM::BASE_DIR . 'Sites/Shop/site_conf.php', $file_contents);

@chmod(OSCOM::BASE_DIR . 'Sites/Shop/site_conf.php', 0644);

$admin_dir_fs_document_root = $dir_fs_document_root . $admin_folder . '/';
$admin_http_path = $http_catalog . $admin_folder . '/';

$file_contents = <<<ENDCFG
<?php
\$ini = <<<EOD
dir_root = "{$admin_dir_fs_document_root}"
http_server = "{$http_server}"
http_path = "{$admin_http_path}"
http_images_path = "images/"
http_cookie_domain = ""
http_cookie_path = "{$admin_http_path}"
EOD;

ENDCFG;
// last empty line needed

file_put_contents(OSCOM::BASE_DIR . 'Sites/Admin/site_conf.php', $file_contents);

@chmod(OSCOM::BASE_DIR . 'Sites/Admin/site_conf.php', 0644);

$modules = [
    [
        'dir' => $dir_fs_document_root . 'includes/modules/payment/',
        'key' => 'MODULE_PAYMENT_INSTALLED',
        'modules' => [
            [
                'file' => 'cod.php',
                'params' => [
                    'MODULE_PAYMENT_COD_SORT_ORDER' => 100
                ]
            ]
        ]
    ],
    [
        'dir' => $dir_fs_document_root . 'includes/modules/shipping/',
        'key' => 'MODULE_SHIPPING_INSTALLED',
        'modules' => [
            [
                'file' => 'flat.php',
                'params' => [
                    'MODULE_SHIPPING_FLAT_SORT_ORDER' => 100
                ]
            ]
        ]
    ],
    [
        'dir' => $dir_fs_document_root . 'includes/modules/order_total/',
        'key' => 'MODULE_ORDER_TOTAL_INSTALLED',
        'modules' => [
            [
                'file' => 'ot_subtotal.php',
                'params' => [
                    'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER' => 100
                ]
            ],
            [
                'file' => 'ot_tax.php',
                'params' => [
                    'MODULE_ORDER_TOTAL_TAX_SORT_ORDER' => 300
                ]
            ],
            [
                'file' => 'ot_shipping.php',
                'params' => [
                    'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER' => 200
                ]
            ],
            [
                'file' => 'ot_total.php',
                'params' => [
                    'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER' => 400
                ]
            ]
        ]
    ],
    [
        'dir' => $dir_fs_document_root . 'includes/modules/action_recorder/',
        'key' => 'MODULE_ACTION_RECORDER_INSTALLED',
        'modules' => [
            [
                'file' => 'ar_admin_login.php'
            ],
            [
                'file' => 'ar_contact_us.php'
            ],
            [
                'file' => 'ar_reset_password.php'
            ],
            [
                'file' => 'ar_tell_a_friend.php'
            ]
        ]
    ],
    [
        'dir' => $dir_fs_document_root . 'includes/modules/social_bookmarks/',
        'key' => 'MODULE_SOCIAL_BOOKMARKS_INSTALLED',
        'modules' => [
            [
                'file' => 'sb_email.php',
                'params' => [
                    'MODULE_SOCIAL_BOOKMARKS_EMAIL_SORT_ORDER' => 100
                ]
            ],
            [
                'file' => 'sb_facebook.php',
                'params' => [
                    'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_SORT_ORDER' => 300
                ]
            ],
            [
                'file' => 'sb_google_plus_share.php',
                'params' => [
                    'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_SHARE_SORT_ORDER' => 200
                ]
            ],
            [
                'file' => 'sb_pinterest.php',
                'params' => [
                    'MODULE_SOCIAL_BOOKMARKS_PINTEREST_SORT_ORDER' => 500
                ]
            ],
            [
                'file' => 'sb_twitter.php',
                'params' => [
                    'MODULE_SOCIAL_BOOKMARKS_TWITTER_SORT_ORDER' => 400
                ]
            ]
        ]
    ],
    [
        'dir' => $dir_fs_document_root . 'includes/modules/navbar_modules/',
        'key' => 'MODULE_CONTENT_NAVBAR_INSTALLED',
        'modules' => [
            [
                'file' => 'nb_hamburger_button.php'
            ],
            [
                'file' => 'nb_brand.php'
            ],
            [
                'file' => 'nb_special_offers.php'
            ],
            [
                'file' => 'nb_currencies.php'
            ],
            [
                'file' => 'nb_account.php'
            ],
            [
                'file' => 'nb_shopping_cart.php'
            ]
        ]
    ],
    [
        'dir' => $dir_fs_document_root . 'includes/modules/header_tags/',
        'key' => 'MODULE_HEADER_TAGS_INSTALLED',
        'modules' => [
            [
                'file' => 'ht_canonical.php',
                'params' => [
                    'MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER' => 400
                ]
            ],
            [
                'file' => 'ht_manufacturer_title.php',
                'params' => [
                    'MODULE_HEADER_TAGS_MANUFACTURER_TITLE_SORT_ORDER' => 100
                ]
            ],
            [
                'file' => 'ht_category_title.php',
                'params' => [
                    'MODULE_HEADER_TAGS_CATEGORY_TITLE_SORT_ORDER' => 200
                ]
            ],
            [
                'file' => 'ht_product_title.php',
                'params' => [
                    'MODULE_HEADER_TAGS_PRODUCT_TITLE_SORT_ORDER' => 300
                ]
            ],
            [
                'file' => 'ht_robot_noindex.php',
                'params' => [
                    'MODULE_HEADER_TAGS_ROBOT_NOINDEX_SORT_ORDER' => 500
                ]
            ],
            [
                'file' => 'ht_datepicker_jquery.php',
                'params' => [
                    'MODULE_HEADER_TAGS_DATEPICKER_JQUERY_SORT_ORDER' => 600
                ]
            ],
            [
                'file' => 'ht_grid_list_view.php',
                'params' => [
                    'MODULE_HEADER_TAGS_GRID_LIST_VIEW_SORT_ORDER' => 700,
                ]
            ],
            [
                'file' => 'ht_table_click_jquery.php',
                'params' => [
                    'MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_SORT_ORDER' => 800
                ]
            ],
            [
                'file' => 'ht_product_colorbox.php',
                'params' => [
                    'MODULE_HEADER_TAGS_PRODUCT_COLORBOX_SORT_ORDER' => 900
                ]
            ],
            [
                'file' => 'ht_noscript.php',
                'params' => [
                    'MODULE_HEADER_TAGS_NOSCRIPT_SORT_ORDER' => 1000
                ]
            ]
        ]
    ],
    [
        'dir' => $admin_dir_fs_document_root . 'includes/modules/dashboard/',
        'key' => 'MODULE_ADMIN_DASHBOARD_INSTALLED',
        'modules' => [
            [
                'file' => 'd_total_revenue.php',
                'params' => [
                    'MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_SORT_ORDER' => 100
                ]
            ],
            [
                'file' => 'd_total_customers.php',
                'params' => [
                    'MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_SORT_ORDER' => 200
                ]
            ],
            [
                'file' => 'd_orders.php',
                'params' => [
                    'MODULE_ADMIN_DASHBOARD_ORDERS_SORT_ORDER' => 300
                ]
            ],
            [
                'file' => 'd_customers.php',
                'params' => [
                    'MODULE_ADMIN_DASHBOARD_CUSTOMERS_SORT_ORDER' => 400
                ]
            ],
            [
                'file' => 'd_admin_logins.php',
                'params' => [
                    'MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_SORT_ORDER' => 500
                ]
            ],
            [
                'file' => 'd_security_checks.php',
                'params' => [
                    'MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_SORT_ORDER' => 600
                ]
            ],
            [
                'file' => 'd_latest_news.php',
                'params' => [
                    'MODULE_ADMIN_DASHBOARD_LATEST_NEWS_SORT_ORDER' => 700
                ]
            ],
            [
                'file' => 'd_latest_addons.php',
                'params' => [
                    'MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_SORT_ORDER' => 800
                ]
            ],
            [
                'file' => 'd_partner_news.php',
                'params' => [
                    'MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_SORT_ORDER' => 820
                ]
            ],
            [
                'file' => 'd_version_check.php',
                'params' => [
                    'MODULE_ADMIN_DASHBOARD_VERSION_CHECK_SORT_ORDER' => 900
                ]
            ],
            [
                'file' => 'd_reviews.php',
                'params' => [
                    'MODULE_ADMIN_DASHBOARD_REVIEWS_SORT_ORDER' => 1000
                ]
            ],
        ]
    ],
    [
        'dir' => $dir_fs_document_root . 'includes/modules/boxes/',
        'key' => 'MODULE_BOXES_INSTALLED',
        'modules' => [
            [
                'file' => 'bm_categories.php',
                'params' => [
                    'MODULE_BOXES_CATEGORIES_SORT_ORDER' => 1000
                ]
            ],
            [
                'file' => 'bm_manufacturers.php',
                'params' => [
                    'MODULE_BOXES_MANUFACTURERS_SORT_ORDER' => 1020
                ]
            ],
            [
                'file' => 'bm_whats_new.php',
                'params' => [
                    'MODULE_BOXES_WHATS_NEW_SORT_ORDER' => 1040
                ]
            ],
            [
                'file' => 'bm_card_acceptance.php',
                'params' => [
                    'MODULE_BOXES_CARD_ACCEPTANCE_SORT_ORDER' => 1060
                ]
            ],
            [
                'file' => 'bm_order_history.php',
                'params' => [
                    'MODULE_BOXES_ORDER_HISTORY_SORT_ORDER' => 5020
                ]
            ],
            [
                'file' => 'bm_best_sellers.php',
                'params' => [
                    'MODULE_BOXES_BEST_SELLERS_SORT_ORDER' => 5030
                ]
            ]
        ]
    ],
    [
        'dir' => $dir_fs_document_root . 'includes/modules/content/',
        'key' => 'MODULE_CONTENT_INSTALLED',
        'modules' => [
            [
                'file' => 'account/cm_account_set_password.php',
                'code' => 'account/cm_account_set_password',
                'params' => [
                    'MODULE_CONTENT_ACCOUNT_SET_PASSWORD_SORT_ORDER' => 100
                ]
            ],
            [
                'file' => 'checkout_success/cm_cs_redirect_old_order.php',
                'code' => 'checkout_success/cm_cs_redirect_old_order',
                'params' => [
                    'MODULE_CONTENT_CHECKOUT_SUCCESS_REDIRECT_OLD_ORDER_SORT_ORDER' => 500
                ]
            ],
            [
                'file' => 'checkout_success/cm_cs_thank_you.php',
                'code' => 'checkout_success/cm_cs_thank_you',
                'params' => [
                    'MODULE_CONTENT_CHECKOUT_SUCCESS_THANK_YOU_SORT_ORDER' => 1000
                ]
            ],
            [
                'file' => 'checkout_success/cm_cs_product_notifications.php',
                'code' => 'checkout_success/cm_cs_product_notifications',
                'params' => [
                    'MODULE_CONTENT_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_SORT_ORDER' => 2000
                ]
            ],
            [
                'file' => 'checkout_success/cm_cs_downloads.php',
                'code' => 'checkout_success/cm_cs_downloads',
                'params' => [
                    'MODULE_CONTENT_CHECKOUT_SUCCESS_DOWNLOADS_SORT_ORDER' => 3000
                ]
            ],
            [
                'file' => 'header/cm_header_logo.php',
                'code' => 'header/cm_header_logo',
                'params' => [
                    'MODULE_CONTENT_HEADER_LOGO_SORT_ORDER' => 10
                ]
            ],
            [
                'file' => 'footer/cm_footer_information_links.php',
                'code' => 'footer/cm_footer_information_links',
                'params' => [
                    'MODULE_CONTENT_FOOTER_INFORMATION_SORT_ORDER' => 0
                ]
            ],
            [
                'file' => 'footer_suffix/cm_footer_extra_copyright.php',
                'code' => 'footer_suffix/cm_footer_extra_copyright',
                'params' => [
                    'MODULE_CONTENT_FOOTER_EXTRA_COPYRIGHT_SORT_ORDER' => 0
                ]
            ],
            [
                'file' => 'footer_suffix/cm_footer_extra_icons.php',
                'code' => 'footer_suffix/cm_footer_extra_icons',
                'params' => [
                    'MODULE_CONTENT_FOOTER_EXTRA_ICONS_SORT_ORDER' => 0
                ]
            ],
            [
                'file' => 'header/cm_header_search.php',
                'code' => 'header/cm_header_search',
                'params' => [
                    'MODULE_CONTENT_HEADER_SEARCH_SORT_ORDER' => 20
                ]
            ],
            [
                'file' => 'header/cm_header_messagestack.php',
                'code' => 'header/cm_header_messagestack',
                'params' => [
                    'MODULE_CONTENT_HEADER_MESSAGESTACK_SORT_ORDER' => 30
                ]
            ],
            [
                'file' => 'header/cm_header_breadcrumb.php',
                'code' => 'header/cm_header_breadcrumb',
                'params' => [
                    'MODULE_CONTENT_HEADER_BREADCRUMB_SORT_ORDER' => 40
                ]
            ],
            [
                'file' => 'index/cm_i_customer_greeting.php',
                'code' => 'index/cm_i_customer_greeting',
                'params' => [
                    'MODULE_CONTENT_CUSTOMER_GREETING_SORT_ORDER' => 100
                ]
            ],
            [
                'file' => 'index/cm_i_text_main.php',
                'code' => 'index/cm_i_text_main',
                'params' => [
                    'MODULE_CONTENT_TEXT_MAIN_SORT_ORDER' => 200
                ]
            ],
            [
                'file' => 'index/cm_i_new_products.php',
                'code' => 'index/cm_i_new_products',
                'params' => [
                    'MODULE_CONTENT_NEW_PRODUCTS_SORT_ORDER' => 300
                ]
            ],
            [
                'file' => 'index/cm_i_upcoming_products.php',
                'code' => 'index/cm_i_upcoming_products',
                'params' => [
                    'MODULE_CONTENT_UPCOMING_PRODUCTS_SORT_ORDER' => 400
                ]
            ],
            [
                'file' => 'index_nested/cm_in_category_description.php',
                'code' => 'index_nested/cm_in_category_description',
                'params' => [
                    'MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_SORT_ORDER' => 100
                ]
            ],
            [
                'file' => 'index_nested/cm_in_category_listing.php',
                'code' => 'index_nested/cm_in_category_listing',
                'params' => [
                    'MODULE_CONTENT_IN_CATEGORY_LISTING_SORT_ORDER' => 200
                ]
            ],
            [
                'file' => 'index_nested/cm_in_new_products.php',
                'code' => 'index_nested/cm_in_new_products',
                'params' => [
                    'MODULE_CONTENT_IN_NEW_PRODUCTS_SORT_ORDER' => 300
                ]
            ],
            [
                'file' => 'login/cm_login_form.php',
                'code' => 'login/cm_login_form',
                'params' => [
                    'MODULE_CONTENT_LOGIN_FORM_SORT_ORDER' => 1000
                ]
            ],
            [
                'file' => 'login/cm_create_account_link.php',
                'code' => 'login/cm_create_account_link',
                'params' => [
                    'MODULE_CONTENT_CREATE_ACCOUNT_LINK_SORT_ORDER' => 2000
                ]
            ],
            [
                'file' => 'navigation/cm_navbar.php',
                'code' => 'navigation/cm_navbar',
                'params' => [
                    'MODULE_CONTENT_NAVBAR_SORT_ORDER' => 10
                ]
            ]
        ]
    ]
];

if (!isset($_POST['DB_SKIP_IMPORT'])) {
    foreach ($modules as $m) {
        $m_installed = [];

        foreach ($m['modules'] as $module) {
            $file = $module['file'];
            $class = isset($module['class']) ? $module['class'] : basename($file, '.php');
            $code = isset($module['code']) ? $module['code'] : $file;

            include($m['dir'] . $file);

            $mo = new $class();
            $mo->install();

            $m_installed[] = $code;

            if (isset($module['params'])) {
                foreach ($module['params'] as $key => $value) {
                    $OSCOM_Db->save('configuration', ['configuration_value' => $value], ['configuration_key' => $key]);
                }
            }
        }

        $OSCOM_Db->save('configuration', ['configuration_value' => implode(';', $m_installed)], ['configuration_key' => $m['key']]);
    }
}
?>

<div class="row">
  <div class="col-sm-9">
    <div class="alert alert-info">
      <h2>New Installation</h2>

      <p>This web-based installation routine will correctly setup and configure osCommerce Online Merchant to run on this server.</p>
      <p>Please follow the on-screen instructions that will take you through the database server, web server, and store configuration options. If help is needed at any stage, please consult the <a href="https://library.oscommerce.com" target="_blank" class="alert-link">osCommerce documentation</a>, seek help at the <a href="http://forums.oscommerce.com" target="_blank" class="alert-link">osCommerce community forums</a>, visit the <a href="https://www.oscommerce.com/Support" target="_blank" class="alert-link">osCommerce support page</a>, or send an enquiry to your server administrator or hosting server provider.</p>
    </div>
  </div>

  <div class="col-sm-3">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <p>Step 4/4</p>

        <ol>
          <li>Database Server</li>
          <li>Web Server</li>
          <li>Online Store Settings</li>
          <li><strong>&gt; Finished!</strong></li>
        </ol>
      </div>
    </div>

    <div class="progress">
      <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">100%</div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-push-3 col-sm-9">
    <h1>Finished!</h1>

    <div class="alert alert-success">The installation of your online store was successful! Click on either button to start your online selling experience:</div>

    <br />

    <div class="row">
      <div class="col-sm-6"><?php echo HTML::button('Online Store (Frontend)', 'fa fa-shopping-cart', $http_server . $http_catalog . 'index.php', array('newwindow' => 1), 'btn-success btn-block'); ?></div>
      <div class="col-sm-6"><?php echo HTML::button('Administration Dashboard (Backend)', 'fa fa-lock', $http_server . $http_catalog . $admin_folder . '/index.php', array('newwindow' => 1), 'btn-info btn-block'); ?></div>
    </div>
  </div>

  <div class="col-xs-12 col-sm-pull-9 col-sm-3">
    <div class="panel panel-success">
      <div class="panel-heading">
        <div class="panel-title">
          Step 4: Finished!
        </div>
      </div>

      <div class="panel-body">
        <p>Congratulations on installing and configuring osCommerce Online Merchant as your online store solution!</p>
        <p>We wish you all the best with the success of your online store and welcome you to join and participate in our community.</p>
        <p>- <a href="https://www.oscommerce.com/Us&Team" target="_blank">The osCommerce Team</a></p>
      </div>
    </div>
  </div>
</div>
