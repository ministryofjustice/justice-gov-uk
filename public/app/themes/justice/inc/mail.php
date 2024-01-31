<?php
// Mail Functions
use Alphagov\Notifications\Client as Client;
use Alphagov\Notifications\Exception\ApiException;

const JUSTICE_MAIL_TEMPLATES = __DIR__ . "/mail-templates.php";

/**
 * Set up the default filter (example)
 *
 * This is a working filter however, you can yse a filter
 * like this to modify email or SMS content.
 *
 * Call it just before you send an email using wp_mail()
 */
function mail_template_default($templates, $attrs)
{
    // default
    $template = $templates['email']['default'];

    // personalisation
    $template['personalisation']['subject'] = $attrs['subject'];
    $template['personalisation']['message'] = $attrs['message'];

    return $template;
}

/**
 * Short-circuits wp_mail()
 * Redirect mail to Gov.UK Notify
 */
add_filter('pre_wp_mail', function ($null, $mail) {
    // Things we'd like to find:
    $patterns = [
        'api' => '/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-[a-f0-9]{4}\-[a-f0-9]{12}/',
        'sms' => '/((\+44(\s\(0\)\s|\s0\s|\s)?)|0)7\d{3}(\s)?\d{6}/' # matches UK mobile numbers
    ];

    // Don't short-circuit if the password doesn't look right
    $maybe_api_key = env('SMTP_PASSWORD') ?? env('SMTP_PASS');
    preg_match_all($patterns['api'], $maybe_api_key, $matches);
    if (count($matches[0]) !== 2) {
        // hand back to wp_mail()
        return null;
    }

    // Set up Gov Notify client
    $client = new Client([
        'apiKey' => $maybe_api_key,
        'httpClient' => new \Http\Adapter\Guzzle7\Client
    ]);

    $templates = require JUSTICE_MAIL_TEMPLATES;

    $settings = mail_template_default($templates, $mail);

    /**
     * Filters the mail template, in the form of Gov Notify args
     *
     * @param array $settings
     */
    if (has_filter('gov_notify_mail_templates')) {
        $settings = apply_filters('gov_notify_mail_templates', $templates, $mail);

        /**
         * Resets the filter hook
         *
         * Always demand a clean filter callback list.
         * There may be a better way of doing this; we are cleaning the callback list to allow closures to
         * pluck templates from the template array. If we don't clean, closures will strip the array clean
         * every time leaving us nothing to 'pluck'. This way, we can safely assume we have a full array of
         * templates to chose from on each closure call.
         */
        remove_all_filters('gov_notify_mail_templates');
    }

    $message = $mail['message'];
    $subject = $mail['subject'];
    $headers = $mail['headers'];
    $attachments = $mail['attachments'];

    $to = '';
    if (isset($mail['to'])) {
        $to = $mail['to'];
    }

    if (!is_array($to)) {
        $to = explode(',', $to);
    }

    $mail_data = compact('to', 'subject', 'message', 'headers', 'attachments', 'settings');

    if (empty($settings)) {
        do_action('wp_mail_failed', new WP_Error('wp_mail_failed', "Gov Notify: No settings were found.", $mail_data));
    }

    foreach ($to as $recipient) {
        // Send!
        try {
            $id = $settings['id'];
            $placeholders = $settings['personalisation'] ?? [];
            $ref = $settings['reference'] ?? '';
            $reply_id = $settings['reply_to_id'] ?? null;

            /**
             * Support SMS and email delivery
             * * * * * * * * * * * * * * * * * * * * */
            $response = !preg_match($patterns['sms'], $recipient)
                ? $client->sendEmail($recipient, $id, $placeholders, $ref, $reply_id)
                : $client->sendSms($recipient, $id, $placeholders, $ref, $reply_id);

            $mail_data['gov_notify_success'] = $response;
            do_action('wp_mail_succeeded', $mail_data);
        } catch (ApiException $ex) {
            $mail_data['gov_notify_exception_code'] = $ex->getCode();
            do_action('wp_mail_failed', new WP_Error('wp_mail_failed', $ex->getMessage(), $mail_data));
        }
    }

    return true;
}, 8, 2);
