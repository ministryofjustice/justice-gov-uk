<?php

namespace MOJ;

// Do not allow access outside WP
defined('ABSPATH') || exit;

/**
 * This class is related to WP_CLI commands for ACF.
 *
 * The reason for `migrate-repeater-fields` is that the in-house custom field
 * implementation used repeater fields stored in postmeta, as serialized arrays.
 * When migrating to ACF, these need to be converted to ACF repeater fields,
 * which are stored differently.
 *
 * The script reads the existing values, and uses the ACF functions to
 * save the fields accordingly. New field names are used, to avoid conflicts,
 * and to allow for testing before switching over.
 *
 * Usage:
 * - wp acf-moj migrate-repeater-fields --dry-run
 * - wp acf-moj migrate-repeater-fields --dry-run=false
 * - wp acf-moj delete-legacy-repeater-fields --dry-run
 * - wp acf-moj delete-legacy-repeater-fields --dry-run=false
 */

use WP_CLI;

class AcfCommands
{

    const REPEATER_FIELDS = [
        [
            'legacy_key' => '_dynamic_menu_additional_entries',
            'field_name' => '_dynamic_menu_additional_entries_acf',
            'field_key' => 'field_696a39ddb33b7',
            'label_field_key' => 'field_696a3a12b33b8',
            'url_field_key' => 'field_696a3a30b33b9',
        ],
        [
            'legacy_key' => '_panel_related_entries',
            'field_name' => '_panel_related_entries_acf',
            'field_key' => 'field_696a71faedf32',
            'label_field_key' => 'field_696a722bedf33',
            'url_field_key' => 'field_696a723dedf34',
        ],
        [
            'legacy_key' => '_panel_other_websites_entries',
            'field_name' => '_panel_other_websites_entries_acf',
            'field_key' => 'field_696a73da021b5',
            'label_field_key' => 'field_696a73da021b6',
            'url_field_key' => 'field_696a73da021b7',
        ]
    ];

    /**
     * Invoke method, for when the command is called.
     */
    public function __invoke($args, $assoc_args): void
    {
        error_reporting(0);

        if (!isset($assoc_args['dry-run'])) {
            WP_CLI::error('The --dry-run argument is required. Please use --dry-run or --dry-run=false.');
            return;
        }

        $dry_run = $assoc_args['dry-run'] !== 'false';

        WP_CLI::log('Running ACF command with dry-run: ' . ($dry_run ? 'true' : 'false'));

        switch ($args[0] ?? '') {
            case 'migrate-repeater-fields':
                WP_CLI::log('Starting migration of repeater fields...');

                $aggregate_logs = [];

                // Loop over REPEATER_FIELDS
                foreach (self::REPEATER_FIELDS as $field) {
                    WP_CLI::log('Migrating field: ' . $field['legacy_key']);

                    // Look in the postmeta table, for rows where meta_key = $field['legacy_key']
                    global $wpdb;
                    $results = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
                            $field['legacy_key']
                        )
                    );

                    foreach ($results as $row) {
                        $post_id = $row->post_id;
                        $meta_value = maybe_unserialize($row->meta_value);

                        if (!is_array($meta_value)) {
                            continue;
                        }

                        // Filter out empty items.
                        $meta_value = array_filter($meta_value, function ($item) {
                            return !(empty($item['label']) && empty($item['url']));
                        });

                        if (empty($meta_value)) {
                            continue;
                        }

                        // Use ACF function to set the repeater field value.
                        $mapped_value = array_map(
                            function ($item) use ($field) {
                                return [
                                    $field['label_field_key'] => $item['label'] ?? '',
                                    $field['url_field_key'] => $item['url'] ?? '',
                                ];
                            },
                            $meta_value
                        );

                        if ($dry_run) {
                            WP_CLI::log("Dry run: Would update post ID $post_id with value: " . print_r($mapped_value, true));
                        } else {
                            update_field($field['field_key'], $mapped_value, $post_id);
                            WP_CLI::log("Updated post ID $post_id");
                        }
                    }

                    $aggregate_logs[] = 'Migrated field: ' . $field['legacy_key'] . ' for ' . count($results) . ' posts.';

                    WP_CLI::log('Completed migration for field: ' . $field['legacy_key']);
                }
                
                WP_CLI::log('Migration completed. Summary:');

                foreach ($aggregate_logs as $log_entry) {
                    WP_CLI::log($log_entry);
                }

                break;

            case 'delete-legacy-repeater-fields':
                WP_CLI::log('Starting deletion of legacy repeater fields...');

                // Delete all rows in the postmeta table, for rows where meta_key = $field['legacy_key']
                foreach (self::REPEATER_FIELDS as $field) {
                    WP_CLI::log('Deleting legacy field: ' . $field['legacy_key']);

                    global $wpdb;
                    if ($dry_run) {
                        $count = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
                                $field['legacy_key']
                            )
                        );
                        WP_CLI::log("Dry run: Would delete $count rows with meta_key = " . $field['legacy_key']);
                    } else {
                        $deleted = $wpdb->query(
                            $wpdb->prepare(
                                "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
                                $field['legacy_key']
                            )
                        );
                        WP_CLI::log("Deleted $deleted rows with meta_key = " . $field['legacy_key']);
                    }
                }

                break;

            default:
                WP_CLI::log('Acf command not recognized');
                break;
        }
    }
}



if (defined('WP_CLI') && WP_CLI) {
    $cluster_helper_commands = new AcfCommands();
    // 1. Register the instance for the callable parameter.
    WP_CLI::add_command('acf-moj', $cluster_helper_commands);

    // 2. Register object as a function for the callable parameter.
    WP_CLI::add_command('acf-moj', 'MOJ\AcfCommands');
}
