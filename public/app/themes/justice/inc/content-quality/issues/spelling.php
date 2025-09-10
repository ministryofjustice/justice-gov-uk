<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

use PhpSpellcheck\Spellchecker\Hunspell;

require_once 'issue.php';
require_once 'spelling.php';

final class ContentQualityIssueSpelling extends ContentQualityIssue
{
    const ISSUE_SLUG = 'spelling';

    const ISSUE_LABEL = 'Incorrect spelling';

    private ?Hunspell $hunspell = null;

    private $dictionary_file = null;

    private ?array $allowed_words = null;


    public function addHooks(): void
    {
        // Call the parent class addHooks method.
        parent::addHooks();

        // Register the settings field for allowed words.
        add_action('admin_init', [$this, 'registerSettingsField']);

        // Create a 1 minute schedule
        add_filter('cron_schedules', [$this, 'addOneMinuteCronSchedule']);

        // Create a scheduled task to run `getPagesWithIssues` every minute.
        add_action('init', function () {
            if (!wp_next_scheduled('moj_content_quality_spelling_cron')) {
                wp_schedule_event(time(), 'one_minute', 'moj_content_quality_spelling_cron');
            }
        });

        // Hook the cron job to the getPagesWithIssues method.
        add_action('moj_content_quality_spelling_cron', [$this, 'getPagesWithIssues']);

        add_action('update_option_moj_content_quality_spelling_allowed_words', [$this, 'handleAllowedWordsUpdate'], 10, 2);
    }



    /**
     * Adds a custom cron schedule of 1 minute.
     *
     * @param array $schedules
     * @return array
     */
    public function addOneMinuteCronSchedule(array $schedules): array
    {
        $schedules['one_minute'] = [
            'interval' => 60,
            'display' => 'Every Minute'
        ];

        return $schedules;
    }


    /**
     * Get the pages with spelling issues.
     *
     * This function retrieves pages with spelling issues by checking the content against the Hunspell dictionary.
     * If a cached/transient value is found in the database, it will be used.
     * If not, it will process the content and cache the results in a transient.
     *
     * The result will be an array in the shape of:
     * [
     *    <page_id> => [<misspelled_word_1>, <misspelled_word_2>, ...],
     *    ...
     * ]
     *
     * @return array An array of pages with spelling issues.
     */
    public function getPagesWithIssues(): array
    {
        // Arrays for appending to later.
        // This will contain the pages with issues.
        $pages_with_issue = [];
        // If pages have been processed then their issues will be appended to this variable, to be saved in the database.
        $transient_updates = [];

        // Has the function been triggered by a cron job?
        $doing_cron = defined('DOING_CRON') && DOING_CRON;
        // If so, lets process a batch of 10 pages at a time.
        // If not, we have been triggered by a browser request, so don't process a batch.
        $process_batch_size = $doing_cron ? 10 : 0;

        global $wpdb;

        $query = "
            SELECT ID, post_content, post_modified, 
                options.option_value AS spelling_issues,
                postmeta.meta_value  AS language
            FROM {$wpdb->posts}
            -- To save us from running get_transient in a php loop, 
            -- we can join the options table to get the transient value here
            LEFT JOIN {$wpdb->options} AS options 
            ON options.option_name = CONCAT('_transient_moj:content-quality:issue:spelling:', ID)
            LEFT JOIN {$wpdb->postmeta} AS postmeta
            ON postmeta.post_id = ID AND postmeta.meta_key = '_language'
            -- Where clauses
            WHERE
                -- options value should be null or not 0
                ( options.option_value IS NULL OR options.option_value != '0' ) AND
                -- Post type should be page 
                post_type = 'page'
                -- Post status should be publish, private or draft
                AND post_status IN ('publish', 'private', 'draft')
        ";

        // Create a counter for how many pages we have processed.
        $processed_count = 0;

        // Loop over every page, and work out if we need to process it's content with the spelling checker.
        foreach ($wpdb->get_results($query) as $page) :

            $path = parse_url(get_permalink($page->ID), PHP_URL_PATH);
            if (preg_match('/^\/news(-\d+)?\//', $path)) {
                // If the path starts with /news/ or /news-<number>/, skip it.
                continue;
            }

            if (isset($page->language) && 'en_GB' !== $page->language) {
                // Skip pages that are not English GB.
                continue;
            }

            // Attempt to unserialize the value of this page's spelling issues.
            $spelling_issues = maybe_unserialize($page->spelling_issues);

            if (is_null($spelling_issues) && $processed_count < $process_batch_size) {
                // Let's process some content...
                if (!$this->allowed_words) {
                    // Get the allowed words from the options table.
                    $allowed_words = get_option('moj_content_quality_spelling_allowed_words', '');
                    $this->allowed_words = array_filter(explode("\n", $allowed_words));
                }

                if (!$this->dictionary_file) {
                    // Get the dictionary file path.
                    $this->dictionary_file = get_template_directory() . '/inc/content-quality/issues/spelling-dictionary.dic';
                }

                // The table didn't contain a transient value, so we need to check the content.
                $spelling_issues = $this->getSpellingIssuesFromContent($page->post_content, $this->allowed_words, $this->dictionary_file);

                // Increase the processed count.
                $processed_count++;

                // Add the value to the transient updates array, this will be used in a bulk update later.
                $transient_updates["$this->transient_key:{$page->ID}"] = serialize($spelling_issues);
            }

            if (is_null($spelling_issues)) {
                // If spelling_issues is still null here, we have reached the batch size, mark the page as queued.
                $spelling_issues = 'queued';
            }

            // If the value is queued, or the array is not empty, add the value to the pages_with_issue array.
            if (!empty($spelling_issues)) {
                $pages_with_issue[$page->ID] = $spelling_issues;
            }
        endforeach;

        if (sizeof($transient_updates)) {
            $expiry = time() + $this->transient_duration;
            $this->bulkSetTransientInDatabase($transient_updates, $expiry);
        }

        return $pages_with_issue;
    }


    /**
     * Append issues for a specific page.
     *
     * This function checks if the page has issues and appends them to the issues array.
     *
     * @param array $issues The current issues array.
     * @param int $post_id The ID of the post to check.
     * @return array The issues array with the anchor issues appended.
     */
    public function appendPageIssues($issues, $post_id)
    {
        // Load the pages with issues - don't run this on construct, as it's an expensive operation.
        $this->loadPagesWithIssues();

        // If we have no issues then, return the issues array as is.
        if (empty($this->pages_with_issue[$post_id])) {
            return $issues;
        }

        // If the issue is 'queued', then append the appropriate message.
        if ('queued' === $this->pages_with_issue[$post_id]) {
            $issues[] = __('The page is queued for spelling issues.', 'justice');
            return $issues;
        }

        // If we are here, then we have an array of issues.
        $count = sizeof($this->pages_with_issue[$post_id]);

        // Construct text to with the number of spelling issues, and the misspelled words.
        $issues[] = sprintf(
            _n('There is %d spelling mistake: %s', 'There are %d spelling mistakes: %s', $count, 'justice'),
            $count,
            implode(', ', $this->pages_with_issue[$post_id])
        );

        return $issues;
    }


    /**
     * Get spelling issues in the content.
     *
     * This function checks the content for spelling issues using the Hunspell library.
     * It strips HTML tags, removes URLs, and checks the content against the Hunspell dictionary.
     *
     * @param string $content The content to check.
     * @return array An array of spelling issues found in the content.
     */
    public function getSpellingIssuesFromContent(string $content, array $allowed_words, ?string $dictionary_file = null): array
    {
        if (empty($content)) {
            return [];
        }

        // Add a space before closing tags
        $content = str_replace('</', ' </', $content);

        // Replace non-breaking spaces with regular spaces
        $content = str_replace('&nbsp;', ' ', $content);

        // Replace line break with spaces
        $content = str_replace("<br>", ' ', $content);

        // Ignore hashtags, as they are not relevant for spelling checks
        $content = preg_replace('/#([a-zA-Z0-9_]+)/', ' ', $content);

        // Remove HTML comments
        $content =  preg_replace('/<!--(.*)-->/Uis', '', $content);

        // Us the wp_kses function to strip all HTML tags.
        $content = \wp_kses($content, [], []);

        // Use regex to remove words that contain numbers.
        $content = preg_replace('/\b\w*[0-9]+\w*\b/', ' ', $content);

        // Ignore ULRs, as they are not relevant for spelling checks
        // URLs are sometimes used as the text part of an a tag, or just plain text.
        // Often they wont have the protocol at the start
        // e.g. 'example.com/wp-content/london'
        $content = preg_replace('/\b[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(?:\/[^\s]*)?\b/', ' ', $content);

        // Loop over the words and remove any allowed words.
        if (!empty($allowed_words)) {
            foreach ($allowed_words as $allowed_word) {
                // Escape the allowed word for regex.
                $allowed_word = preg_quote($allowed_word, '/');
                // Remove the allowed word from the content.
                $content = preg_replace('/\b' . $allowed_word . '(?!\w)/i', ' ', $content);
            }
        }

        if (!($this->hunspell instanceof Hunspell)) {

            $this->hunspell = Hunspell::create();

            if ($dictionary_file && file_exists($dictionary_file)) {
                // Start a workaround to set the dictionary file.
                // This is needed because the Hunspell class does not allow setting the dictionary file directly.

                // 1. Get the binary path.
                $binary_path = $this->hunspell->getBinaryPath();

                // 2. Add the dictionary file as an argument.
                $binary_path = $binary_path->addArgs(['-p', $dictionary_file]);

                // 3. Reconstruct the Hunspell instance with the new binary path.
                // Note that `__construct` is a public method, so we can call it directly.
                $this->hunspell->__construct($binary_path);
            } else if ($dictionary_file) {
                error_log("Spelling dictionary file not found: $dictionary_file");
            }
        }

        $spelling_issues = [];

        $misspellings = $this->hunspell->check($content, ['en_GB-large']);

        $misspelling_words = array_map(fn($misspelling) => $misspelling->getWord(), iterator_to_array($misspellings));

        // TODO - tidy this up

        $misspellings_starting_or_ending_with_quote = array_filter(
            $misspelling_words,
            fn($misspelling) => (str_starts_with($misspelling, "'") || str_ends_with($misspelling, "'"))
        );

        // Remove the quotes and try again.
        $retry_words = array_map(fn($misspelling) => trim($misspelling, "'"), $misspellings_starting_or_ending_with_quote);

        $retry_misspellings = $this->hunspell->check(implode(' ', $retry_words), ['en_GB-large']);

        // Remove $misspellings_starting_or_ending_with_quote from $misspelling_words
        $misspelling_words = array_diff($misspelling_words, $misspellings_starting_or_ending_with_quote);

        // Add the retry misspellings to the misspelling words.
        $misspelling_words = array_merge($misspelling_words, array_map(fn($misspelling) => $misspelling->getWord(), iterator_to_array($retry_misspellings)));

        // error_log('Misspellings found: ' . implode(', ', $misspellings_starting_or_ending_with_quote));

        // Order alphabetically
        sort($misspelling_words);

        foreach ($misspelling_words as $word) {

            if (!in_array($word, $spelling_issues)) {
                $spelling_issues[] = $word;
            }
        }

        return $spelling_issues;
    }


    // - - - - - - - - - - - - - - - - - - - - - - - - - -
    // ⬇️ Settings for allowed words ⬇️ 
    // 
    // The functions below are related to allowing words 
    // that are not in the dictionary. 
    // - - - - - - - - - - - - - - - - - - - - - - - - - -


    /**
     * Register the settings field for allowed words, on the Content Quality Settings page.
     * 
     * This function registers the settings field for allowed words in the content quality options page.
     * It adds a settings field to the 'moj_content_quality_spelling_options' section
     * and renders the allowed words field.
     *
     * @return void
     */
    public function registerSettingsField(): void
    {
        // Register the settings for the options page.
        register_setting('moj_content_quality_spelling_options', 'moj_content_quality_spelling_allowed_words', [
            'sanitize_callback' => fn($input) => $this->allowedSpellingSanitization(sanitize_textarea_field($input))
        ]);

        // Add settings section.
        add_settings_section(
            'moj_content_quality_spelling_section',
            __('Content Quality Settings', 'justice'),
            null,
            'moj_content_quality_spelling_options'
        );

        // Add settings field.
        add_settings_field(
            'moj_content_quality_spelling_allowed_words',
            __('Allowed Words', 'justice'),
            [$this, 'renderAllowedWordsField'],
            'moj_content_quality_spelling_options',
            'moj_content_quality_spelling_section'
        );
    }


    /**
     * Render the allowed words field.
     * 
     * @return void
     */
    public function renderAllowedWordsField(): void
    {
        // Load the pages with issues - don't run this on construct, as it's an expensive operation.
        $this->loadPagesWithIssues();

        $allowed_words = get_option('moj_content_quality_spelling_allowed_words', '');

        printf(
            '<textarea name="moj_content_quality_spelling_allowed_words" rows="10" cols="50">%s</textarea>',
            esc_textarea($allowed_words)
        );

        printf(
            '<p class="description">%s<br/>%s</p>',
            esc_html__('Enter words that should be allowed even if they are not in the dictionary.', 'justice'),
            esc_html__('Words should be entered one per line.', 'justice')
        );

        if (empty($this->pages_with_issue)) {
            return;
        }

        // Use filter to get the pages that are queued for spelling issues.
        $pages_in_queue = array_filter($this->pages_with_issue, fn($issues) => $issues === 'queued');

        if(sizeof($pages_in_queue)) {
            printf(
                '<p class="description">%s</p>',
                sprintf(
                    esc_html__('There are currently %d pages being processed for spelling issues.', 'justice'),
                    count($pages_in_queue)
                )
            );
        }

        // Filter out any entries with the value 'queued'.
        $pages_with_issue = array_filter($this->pages_with_issue, fn($issues) => $issues !== 'queued');

        // Let's get the misspelled words from the pages with issues.
        $misspelled_words = array_unique(array_merge(...array_values($pages_with_issue)));

        if (empty($misspelled_words)) {
            return;
        }

        // Order alphabetically
        sort($misspelled_words);

        printf(
            '<p class="description">%s</p>',
            sprintf(
                esc_html__('The following words (%d) are currently marked as misspelled: %s', 'justice'),
                count($misspelled_words),
                '<strong>' . implode(', ', $misspelled_words) . '</strong>'
            )
        );
    }


    /**
     * Sanitize the allowed spelling words input.
     *
     * @param string $input The input string to sanitize.
     * @return string The sanitized string with allowed words, one per line.
     */
    public function allowedSpellingSanitization(string $input): string
    {
        // Replace spaces with new lines.
        $input = preg_replace('/\s+/', "\n", $input);

        // Split the input into lines.
        $lines = explode("\n", $input);

        // Trim punctuation and spaces from each line.
        $lines = array_map(fn($line) => trim(trim(trim($line, ','), '.'), ' '), $lines);

        // Filter out empty lines.
        $lines = array_filter($lines, fn($line) => !empty($line));

        // Remove duplicates
        $lines = array_unique($lines);

        // Sort the lines alphabetically.
        sort($lines);

        // Convert the lines back to a single string.
        $string = implode("\n", $lines);

        return $string;
    }


    /**
     * Handle the update of allowed words.
     *
     * This function is triggered when the allowed words option is updated.
     * It checks if any words have been removed from the allowed list and deletes the transients
     * for those pages. If words have been added, it finds the pages with spelling issues
     * related to the added words and deletes the transients for those pages so they can be
     * reprocessed.
     *
     * @param string $old_value The old value of the allowed words.
     * @param string $value The new value of the allowed words.
     * @return void
     */
    public function handleAllowedWordsUpdate($old_string = '', $new_string = ''): void
    {
        // Get an array of words from the old and new strings.
        $old_lines = array_filter(array_map('trim', explode("\n", $old_string)));
        $new_lines = array_filter(array_map('trim', explode("\n", $new_string)));

        // Which words have been removed from the allowed list?
        $removed_words = array_diff($old_lines, $new_lines);

        // If any words have been removed from the allowed list, then we need to delete the transients to reprocess all pages.
        if (sizeof($removed_words)) {
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_moj:content-quality:issue:spelling:%'");
            // Return early, we don't need to do anything else.
            return;
        }

        // If we are here, then words have been added to the allowed list.
        // We need to find the pages that have spelling issues related to the added words and delete
        // the transients for those pages - so that they can be reprocessed.

        $this->loadPagesWithIssues();

        // If the value is empty, we don't need to delete the transient.
        if (empty($this->pages_with_issue)) {
            error_log('No pages with spelling issues found, no transients to delete.');
            return;
        }

        // Filter out any entries with the value 'queued'.
        $pages_with_issue = array_filter($this->pages_with_issue, fn($issues) => $issues !== 'queued');

        // Filter out unrelated issues.
        $pages_with_issues_related_to_added_words = array_filter(
            $pages_with_issue,
            fn($issues) => !empty(array_intersect($issues, $new_lines))
        );

        foreach ($pages_with_issues_related_to_added_words as $page_id => $issues) {
            error_log('Deleting transient for page ID: ' . $page_id);
            $this->deleteTransientFromDatabase($this->transient_key . ':' . $page_id);
        }
    }
}
