<?php
/**
 * Tossee - Ping Endpoint
 * Simple health check endpoint
 */

define('TOSSEE_API', true);

header('Content-Type: text/plain');
echo "PING OK\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "Tossee Chat API is running\n";
