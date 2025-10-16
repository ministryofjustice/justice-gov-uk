<?php

namespace MOJ;

use Roots\WPConfig\Config;

defined('ABSPATH') || exit;

class AV
{
    public static function init(): void
    {
        // Intentionally left blank; this class is just a namespace for the file.

        add_filter('wp_handle_upload_prefilter', function ($file) {
            $tmp = $file['tmp_name'] ?? null;
            if (!$tmp || !is_file($tmp)) {
                return $file; // nothing to scan
            }

            // Example: call out to a local clamd TCP service (see next snippet) or a scanning API
            $result = self::scanWithClam($tmp); // returns ['ok'=>true] or ['ok'=>false,'sig'=>'Mal/Example']
            if (!$result['ok']) {
                $file['error'] = 'Upload blocked: malware detected (' . esc_html($result['sig']) . ').';
            }
            return $file;
        });
    }

    // Generated my M365 Copilot with GPT-5 turned on.
    public static function scanWithClam(string $path): array
    {
        $clam_hostname = Config::get('CLAM_HOSTNAME');

        $sock = @fsockopen($clam_hostname, 3310, $errno, $errstr, 2.0);

        if (!$sock) {
            // Decide your policy: fail-closed (block) or fail-open (allow)
            return ['ok' => false, 'sig' => 'ScannerUnavailable'];
        }

        stream_set_timeout($sock, 30);
        fwrite($sock, "nINSTREAM\n");
        $h = fopen($path, 'rb');
        while (!feof($h)) {
            $chunk = fread($h, 8192);
            $len = pack('N', strlen($chunk));
            fwrite($sock, $len . $chunk);
        }
        fwrite($sock, pack('N', 0)); // terminator
        $resp = fgets($sock);
        fclose($h);
        fclose($sock);

        if ($resp !== false && strpos($resp, 'FOUND') !== false) {
            // Format: "stream: Threat.Name FOUND"
            if (preg_match('/^stream: (.+) FOUND$/', $resp, $m)) {
                return ['ok' => false, 'sig' => $m[1]];
            }
            return ['ok' => false, 'sig' => 'Malware'];
        }

        error_log('AV scan result: ' . trim((string) $resp));
        return ['ok' => true];
    }
}

AV::init();
