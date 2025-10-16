<?php

/**
 * Antivirus Scanner Integration for WordPress
 *
 * This must-use plugin integrates ClamAV antivirus scanning into WordPress file uploads.
 * It scans all uploaded files before they are processed and blocks malicious content.
 *
 * @package MOJ\Justice
 * @since 1.0.0
 */

namespace MOJ;

use Roots\WPConfig\Config;

defined('ABSPATH') || exit;

/**
 * Antivirus Scanner Class
 *
 * Provides integration with ClamAV daemon for real-time file scanning.
 * Hooks into WordPress upload process to scan files before they are processed.
 */
class AV
{
    /**
     * Initialize the antivirus scanner
     *
     * Sets up WordPress hooks for file scanning if AV is not disabled.
     * Can be disabled by setting CLAM_DISABLED=true in environment.
     *
     * @return void
     */
    public static function init(): void
    {
        // Exit early if antivirus scanning is disabled
        if (Config::get('CLAM_DISABLED') === 'true') {
            return;
        }

        // Hook into WordPress file upload process
        add_filter('wp_handle_upload_prefilter', function ($file) {
            $tmp = $file['tmp_name'] ?? null;
            
            // Skip scanning if no file or file doesn't exist
            if (!$tmp || !is_file($tmp)) {
                return $file;
            }

            // Scan the uploaded file with ClamAV
            $result = self::scanWithClam($tmp);
            
            // Block upload if malware is detected
            if (!$result['ok']) {
                $file['error'] = 'Upload blocked: ' . esc_html($result['sig']) . '.';
            }
            
            return $file;
        });
    }

    /**
     * Scan a file with ClamAV daemon
     *
     * Connects to ClamAV daemon via TCP socket and streams the file content
     * for real-time scanning. Uses the INSTREAM command for efficient scanning
     * without requiring file system access from the ClamAV container.
     *
     * @param string $path Full path to the file to be scanned
     * @return array Scan result with keys:
     *               - 'ok' (bool): true if file is clean, false if infected/error
     *               - 'sig' (string): malware signature name if infected
     */
    public static function scanWithClam(string $path): array
    {
        $clam_hostname = Config::get('CLAM_HOSTNAME');

        // Establish TCP connection to ClamAV daemon (clamd)
        $sock = @fsockopen($clam_hostname, 3310, $errno, $errstr, 2.0);

        if (!$sock) {
            // Fail-closed security policy: block uploads when scanner is unavailable
            // This prevents malware uploads during scanner downtime
            error_log("ClamAV connection failed: $errstr ($errno)");
            return ['ok' => false, 'sig' => 'Malware Scanner Unavailable'];
        }

        // Set socket timeout to prevent hanging on large files
        stream_set_timeout($sock, 30);
        
        // Send INSTREAM command to initiate streaming scan
        fwrite($sock, "nINSTREAM\n");
        
        // Stream file contents to ClamAV in chunks
        $h = fopen($path, 'rb');
        while (!feof($h)) {
            $chunk = fread($h, 8192);
            // Send chunk length (4 bytes, network byte order) followed by chunk data
            $len = pack('N', strlen($chunk));
            fwrite($sock, $len . $chunk);
        }
        
        // Send terminator (zero-length chunk) to signal end of stream
        fwrite($sock, pack('N', 0));
        
        // Read scan result from ClamAV
        $resp = fgets($sock);
        
        // Clean up file and socket resources
        fclose($h);
        fclose($sock);

        // Parse ClamAV response
        if ($resp !== false && strpos($resp, 'FOUND') !== false) {
            // Malware detected - extract signature name
            // Expected format: "stream: Threat.Name FOUND"
            if (preg_match('/^stream: (.+) FOUND$/', $resp, $m)) {
                error_log("Malware detected: {$m[1]} in file: $path");
                return ['ok' => false, 'sig' => "Malware Detected - {$m[1]}"];
            }
            // Fallback for unexpected FOUND response format
            error_log("Malware detected (unknown signature) in file: $path");
            return ['ok' => false, 'sig' => 'Malware Detected'];
        }

        // Log scan results for monitoring (expected: "stream: OK")
        error_log('AV scan result: ' . trim((string) $resp));
        
        // File is clean
        return ['ok' => true];
    }
}

// Initialize the antivirus scanner when this mu-plugin loads
AV::init();
