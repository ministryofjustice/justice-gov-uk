<?php
/**
 * An example command - this command will operate on the command line
 * Usage:
 *  dry-run: wp my-custom-command
 *  real-run: wp my-custom-command execute
 */

class MyCustomCommand
{

    /**
     * @var wpdb|QM_DB
     */
    public wpdb|QM_DB $db;

    /**
     * @var false
     */
    private bool $dry_run;

    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb; // load the DB interface
        $this->dry_run = true;
    }

    /**
     * REQUIRED
     * WP_CLI executes this method
     * @param $args
     * @return void
     */
    public function __invoke($args): void
    {
        error_reporting(0);

        $do_fix = $args[0] ?? '';

        if ($do_fix === 'execute') {
            $this->dry_run = false;
        }

        $this->active();
    }

    /**
     * Load methods in a logical manner
     */
    private function active(): void
    {
        $this->warning();
        $this->prepare();
        $this->update();
        $this->report();
    }

    private function warning(): void
    {
        if (!$this->dry_run) {
            $this->message("\nAre you sure you want to continue?", 'confirm');
            $this->message("\n---------------------------------\n");
            $this->message("We will attempt to run the script.\n", 'warning');

            sleep(3);

            $this->message("\n---------------------------------\n");
            $this->message("Beginning now...");
        }
    }

    private function prepare(): void
    {

    }

    private function update(): void
    {

    }

    private function report(): void
    {

    }

    private function message($message, $status = 'log'): void
    {
        if (is_array($message)) {
            $message = sprintf(key($message), $message[key($message)]);
        }

        switch ($status) {
            case 'confirm':
                WP_CLI::confirm($message);
                break;
            case 'success':
                WP_CLI::success($message);
                break;
            case 'warning':
                WP_CLI::warning($message);
                break;
            case 'log':
                WP_CLI::log($message);
                break;

        }
    }
}

// 1. Register the instance for the callable parameter.
$instance = new MyCustomCommand();
WP_CLI::add_command('my-custom-command', $instance);

// 2. Register object as a function for the callable parameter.
WP_CLI::add_command('my-custom-command', 'DocumentRevisionReconcile');
