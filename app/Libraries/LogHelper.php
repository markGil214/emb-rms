<?php

namespace App\Libraries;

/**
 * ✅ STRUCTURED LOGGING HELPER
 * 
 * Formats logs as JSON for integration with:
 * - ELK Stack (Elasticsearch, Logstash, Kibana)
 * - Datadog
 * - CloudWatch
 * - Splunk
 * 
 * Structured logs are:
 * - Machine-parseable (JSON)
 * - Consistent format
 * - Easy to query and alert on
 * - Essential for debugging production issues
 */
class LogHelper
{
    /**
     * Log structured event to info level
     */
    public static function info($event, $data = [])
    {
        $log = self::format('info', $event, $data);
        log_message('info', $log);
    }

    /**
     * Log structured event to warning level
     */
    public static function warning($event, $data = [])
    {
        $log = self::format('warning', $event, $data);
        log_message('warning', $log);
    }

    /**
     * Log structured event to error level
     */
    public static function error($event, $data = [])
    {
        $log = self::format('error', $event, $data);
        log_message('error', $log);
    }

    /**
     * Log security event (potential abuse/attack)
     */
    public static function security($event, $data = [])
    {
        $data['security_alert'] = true;
        $log = self::format('error', $event, $data);
        log_message('error', $log);
    }

    /**
     * Format log as JSON with context
     */
    protected static function format($level, $event, $data = [])
    {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'timezone' => app_timezone(),
            'level' => strtoupper($level),
            'event' => $event,
            'user_id' => auth_user()['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            'data' => $data,
        ];

        return json_encode($log, JSON_UNESCAPED_SLASHES);
    }
}
