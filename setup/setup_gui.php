<?php
    namespace PHPAnt\Setup;

    $deps = [ dirname(__DIR__) . '/includes/classes/Installer.class.php',
              dirname(__DIR__) . '/includes/classes/BaseSetupConfigs.class.php',
              dirname(__DIR__) . '/includes/classes/InteractiveConfigs.class.php'
    ];

    foreach($deps as $dep) {
        if(file_exists($dep) == false) die("Cannot find dependency: " . $dep);
        include($dep);
    }


    session_start();
    if(isset($_SESSION['phpant_nonce']) == false) $_SESSION['phpant_nonce'] = null;

    $nonce = ($_SESSION['phpant_nonce'] == null ?  bin2hex(random_bytes(32)) : $_SESSION['phpant_nonce'] );
    $_SESSION['phpant_nonce'] = $nonce;

    function processInstallation() {
        if(strcmp($_POST['nonce'], $_SESSION['phpant_nonce']) !== 0) die("Installation failed. Invalid nonce.");

        //Destroy the nonce. We are about to use it.

        //Prepare the json installation data so we can pass it to the installer.

        $http_host     = filter_var($_POST[ 'weburl'  ],FILTER_VALIDATE_URL);
        $document_root = filter_var($_POST[ 'webroot' ],FILTER_SANITIZE_STRING);
        $server        = filter_var($_POST[ 'server'  ], FILTER_SANITIZE_STRING);
        $database      = filter_var($_POST[ 'database'], FILTER_SANITIZE_STRING);
        $rootpass      = filter_var($_POST[ 'rootpass'], FILTER_SANITIZE_STRING);
        $username      = filter_var($_POST[ 'username'], FILTER_SANITIZE_STRING);
        $userpass      = filter_var($_POST[ 'adminpass'], FILTER_SANITIZE_STRING);

        $adminfirst    = filter_var($_POST[ 'adminfirst'], FILTER_SANITIZE_STRING);
        $adminlast     = filter_var($_POST[ 'adminlast'], FILTER_SANITIZE_STRING);
        $adminuser     = filter_var($_POST[ 'adminuser'], FILTER_SANITIZE_STRING);
        $adminpass     = filter_var($_POST[ 'adminpass'], FILTER_SANITIZE_STRING);

        $settings = [];
        $settings['http_host']     = $http_host;
        $settings['document_root'] = $document_root;

        $db = [];
        $db['server']   = $server  ;
        $db['database'] = $database;
        $db['rootuser'] = 'root';
        $db['rootpass'] = $rootpass;
        $db['username'] = $username;
        $db['userpass'] = $userpass;

        $settings['db']            = $db;

        $adminUserInfo = [];

        $adminUserInfo['first']    = $adminfirst;
        $adminUserInfo['last']     = $adminlast ;
        $adminUserInfo['username'] = $adminuser ;
        $adminUserInfo['password'] = $adminpass ;

        $settings['adminuser'] = $adminUserInfo;

        $apps = [];

        //Add the app manager.
        $app = [];
        $app['remote'] = 'https://github.com/mjmunger/phpant-app-manager.git';
        $apps[] = $app;

        //Add default grammar.
        $app = [];
        $app['remote'] = 'https://github.com/mjmunger/ant-app-default.git';
        $apps[] = $app;

        $settings['apps'] = $apps;

        //Save this to a temp file, which we'll destroy later.
        $settings_path = tempnam('/tmp/',"ant_");

        $WebConfigs = new InteractiveConfigs(dirname(__DIR__));
        $WebConfigs->configs = json_decode(json_encode($settings));
        $Installer = new Installer($WebConfigs);

        $Installer->install();

        header("location: /");



    }

    if(isset($_POST['nonce']) && $_POST['nonce'] !== null) processInstallation();

?>
<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>PHP-Ant Setup</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="setup/css/setup.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

</head>
<body>
<!--[if lte IE 9]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
<![endif]-->

<!-- Add your site or application content here -->
    <div class="w3-container setup-card">
        <div class="w3-card-4">

            <div class="w3-container w3-blue">
                <h2>PHP-Ant Setup</h2>
                <p class="w3-small">Enter the database connection and setup information to configure PHP-Ant. Also include the root administrator user information.</p>
            </div>

            <form class="w3-container" method="post" id="installInfoForm">
                <h3>Database connection information</h3>

                <label>Database Server</label>
                <input class="w3-input w3-border" name="server" id="server" type="text" " value="<?php echo getenv('PHPANT_DB_HOST'); ?>">

                <label>Root Password</label>
                <input class="w3-input w3-border" name="rootpass" id="rootpass" type="text" value="<?php echo getenv('PHPANT_DB_ROOTPW'); ?>">

                <label>Database Name</label>
                <input class="w3-input w3-border" name="database" id="database" type="text" value="<?php echo getenv('PHPANT_DB_NAME'); ?>">

                <label>Database Username</label>
                <input class="w3-input w3-border" name="username" id="username" type="text" value="<?php echo getenv('PHPANT_DB_USER'); ?>">

                <label>Database Password</label>
                <input class="w3-input w3-border" name="password" id="password" type="text" value="<?php echo getenv('PHPANT_DB_PASS'); ?>">

                <h3>Website information</h3>
                <label>Website URL</label>
                <input class="w3-input w3-border" type="text" id="weburl" name="weburl" placeholder="https://example.org">

                <label>Website Document Root </label>
                <input class="w3-input w3-border" type="text" id="webroot" name="webroot" placeholder="https://example.org" value="<?php echo dirname(__DIR__) . '/'; ?>">
                <div class="w3-small w3-red" id="nochangewebroot" style="display:none">Don't change this unless you know what you're doing!</div>


                <h3>Administrative user information</h3>

                <label>Admin User's First Name</label>
                <input class="w3-input w3-border" type="text" name="adminfirst" id="adminfirst" >

                <label>Admin User's Last Name</label>
                <input class="w3-input w3-border" type="text" name="adminlast" id="adminlast">

                <label>Admin User (email address is best)</label>
                <input class="w3-input w3-border" type="text" name="adminuser" id="adminuser" value="<?= getenv('PHPANT_ADMIN_USER') ?>">

                <label>Admin User Password</label>
                <input class="w3-input w3-border" type="text" name="adminpass" id="adminpass" ">

                <input type="hidden" name="nonce" id="nonce" value="<?php echo $nonce; ?>">

                <div id="error-list">

                </div>

                <button class="w3-button w3-blue w3-right" id="install" style="margin-top:1rem; margin-bottom:1rem;">Install</button>
            </form>

        </div>
    </div>

<script src="/setup/js/setup.js"></script>
<script>
    // self executing function here
    (function() {

        let elem = document.getElementById('weburl');
        elem.value = document.location;

        console.log(elem);

        elem = document.getElementById('adminpass');
        let envpass = "<?= getenv('PHPANT_ADMIN_PASS') ?>";

        elem.value = (envpass.length > 0 ? envpass : generatePassword());

        let webroot = document.getElementById('webroot');
        webroot.onfocus = function() {
            let x = document.getElementById('nochangewebroot');
            x.style.display = 'block';
        };

        webroot.onblur = function() {
            let x = document.getElementById('nochangewebroot');
            x.style.display = 'none';
        };

        let install = document.getElementById('install');
        install.onclick = function(e) {
            e.preventDefault();

            if(validateForm()) {
                let f = document.getElementById('installInfoForm');
                f.submit();
            }
        }

    })();
</script>
</body>
</html>
<?php die(); ?>