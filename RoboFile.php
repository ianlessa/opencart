<?php
require_once 'vendor/autoload.php';

if (file_exists('.env')) {
    $dotenv = new Dotenv\Dotenv(__DIR__);
    $dotenv->load();
}

class RoboFile extends \Robo\Tasks
{
    // use \Robo\Task\Development\loadTasks;
    use \Robo\Common\TaskIO;

    public function __construct()
    {
        $this->opencart_config = [
            'db_hostname' => getenv('DB_HOST'),
            'db_username' => getenv('DB_USER'),
            'db_password' => getenv('DB_PASSWORD'),
            'db_database' => getenv('DB_NAME'),
            'db_driver' => getenv('DB_DRIVER'),
            'username' => getenv('OC_ADMIN_USER'),
            'password' => getenv('OC_ADMIN_PASS'),
            'email' => getenv('OC_ADMIN_MAIL'),
            'http_server' => getenv('HTTP_SERVER')
        ];
    }

    public function opencartSetup()
    {
        if (getenv('REGENERATE_OPENCART_DIR')) {
            $this->taskDeleteDir(getenv('OC_ROOT'))->run();
            $this->taskFileSystemStack()
                ->mirror('vendor/opencart/opencart/upload', getenv('OC_ROOT'))
                ->run();
        }

        $this->taskFileSystemStack()
            ->copy('vendor/beyondit/opencart-test-suite/src/upload/system/config/test-config.php', getenv('OC_ROOT') . 'system/config/test-config.php')
            ->copy('vendor/beyondit/opencart-test-suite/src/upload/system/library/session/test.php', getenv('OC_ROOT') . 'system/library/session/test.php')
            ->copy('vendor/beyondit/opencart-test-suite/src/upload/admin/controller/startup/test_startup.php', getenv('OC_ROOT') . 'admin/controller/startup/test_startup.php')
            ->run();

        if (getenv('REGENERATE_OPENCART_DIR')) {
            $this->taskFileSystemStack()
                ->chmod(getenv('OC_ROOT'), 0777, 0000, true);

            // Create new database, drop if exists already
            try {
                $conn = new PDO("mysql:host=" . $this->opencart_config['db_hostname'], $this->opencart_config['db_username'], $this->opencart_config['db_password']);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->exec("DROP DATABASE IF EXISTS `" . $this->opencart_config['db_database'] . "`");
                $conn->exec("CREATE DATABASE `" . $this->opencart_config['db_database'] . "`");
            } catch (PDOException $e) {
                $this->say("<error> Database error: " . $e->getMessage());
            }

            $install = $this->taskExec('php')->arg(getenv('OC_ROOT') . 'install/cli_install.php')->arg('install');
            foreach ($this->opencart_config as $option => $value) {
                $install->option($option, $value);
            }
            $install->rawArg('> /dev/null');

            $install->run();

            if (!is_dir('.modman')) {
                $this->taskExec('vendor/bin/modman init ' . getenv('OC_ROOT'))->run();
                $this->taskExec('ln -sf "$(pwd)" .modman/')->run();
            }

            $this->taskExec('vendor/bin/modman deploy-all --no-clean > /dev/null')->run();
        }
    }
}
