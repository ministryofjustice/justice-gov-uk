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
 * To select a different template, use the filter just before you send an email using wp_mail()
 *
 * @example
 * add_filter('moj_mail_template', function ($templates) use ($local_var_one, $local_var_two) {
 *     $template = $templates['email']['hello-world'];
 *     $template['personalisation']['name'] = $local_var_one;
 *     $template['personalisation']['email'] = $local_var_two;
 *     return $template;
 * }, 10, 1);
 *
 * wp_mail($local_var_two, 'default', 'default');
 *
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
        'api' => '/[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[a-f0-9]{4}-[a-f0-9]{12}/',
        'sms' => '/((\+44(\s\(0\)\s|\s0\s|\s)?)|0)7\d{3}(\s)?\d{6}/' # matches UK mobile numbers
    ];

    // Don't short-circuit if the password doesn't look right
    $api_key = env('GOV_NOTIFY_API_KEY');
    preg_match_all($patterns['api'], $api_key, $matches);
    if (count($matches[0]) !== 2) {
        // hand back to wp_mail()
        return null;
    }

    // Set up Gov Notify client
    $client = new Client([
        'apiKey' => $api_key,
        'httpClient' => new \Http\Adapter\Guzzle7\Client
    ]);

    $templates = require JUSTICE_MAIL_TEMPLATES;

    $settings = mail_template_default($templates, $mail);

    /**
     * Filters the mail template, in the form of Gov Notify args
     *
     * @param array $settings
     */
    if (has_filter('moj_mail_template')) {
        $settings = apply_filters('moj_mail_template', $templates, $mail);

        /**
         * Resets the filter hook
         *
         * Always demand a clean filter callback list.
         * There may be a better way of doing this; we are cleaning the callback list to allow closures to
         * pluck templates from the template array. If we don't clean, closures will strip the array clean
         * every time leaving us nothing to 'pluck'. This way, we can safely assume we have a full array of
         * templates to chose from on each closure call.
         */
        remove_all_filters('moj_mail_template');
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
