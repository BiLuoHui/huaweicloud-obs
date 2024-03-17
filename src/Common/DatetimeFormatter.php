<?php

namespace Bihuohui\HuaweicloudObs\Common;

use DateTime;
use DateTimeZone;
use Exception;

class DatetimeFormatter
{
    protected static ?DateTimeZone $timeZone;

    /**
     * @param $fmt
     * @param $value
     * @return int|mixed|string|null
     * @throws Exception
     */
    public static function format($fmt, $value): mixed
    {
        if ($fmt === 'date-time') {
            return DatetimeFormatter::formatDateTime($value);
        }

        if ($fmt === 'data-time-http') {
            return DatetimeFormatter::formatDateTimeHttp($value);
        }

        if ($fmt === 'data-time-middle') {
            return DatetimeFormatter::formatDateTimeMiddle($value);
        }

        if ($fmt === 'date') {
            return DatetimeFormatter::formatDate($value);
        }

        if ($fmt === 'timestamp') {
            return DatetimeFormatter::formatTimestamp($value);
        }

        if ($fmt === 'boolean-string') {
            return DatetimeFormatter::formatBooleanAsString($value);
        }

        return $value;
    }

    /**
     * @param $value
     * @return string|null
     * @throws Exception
     */
    private static function formatDateTime($value): ?string
    {
        return self::dateFormatter($value, 'Y-m-d\TH:i:s\Z');
    }

    /**
     * @param        $dt
     * @param string $fmt
     * @return string|null
     * @throws Exception
     */
    private static function dateFormatter($dt, string $fmt): ?string
    {
        if (is_numeric($dt)) {
            return gmdate($fmt, (int)$dt);
        }

        if (is_string($dt)) {
            $dt = new DateTime($dt);
        }

        if ($dt instanceof DateTime) {
            if (!DatetimeFormatter::$timeZone) {
                DatetimeFormatter::$timeZone = new DateTimeZone('UTC');
            }

            return $dt->setTimezone(DatetimeFormatter::$timeZone)->format($fmt);
        }

        return null;
    }

    /**
     * @param $value
     * @return string|null
     * @throws Exception
     */
    private static function formatDateTimeHttp($value): ?string
    {
        return self::dateFormatter($value, 'D, d M Y H:i:s \G\M\T');
    }

    /**
     * @param $dateTime
     * @return string|null
     * @throws Exception
     */
    private static function formatDateTimeMiddle($dateTime): ?string
    {
        if (is_string($dateTime)) {
            $dateTime = new DateTime($dateTime);
        }

        if ($dateTime instanceof DateTime) {
            return $dateTime->format('Y-m-d\T00:00:00\Z');
        }

        return null;
    }

    /**
     * @param $value
     * @return string|null
     * @throws Exception
     */
    private static function formatDate($value): ?string
    {
        return self::dateFormatter($value, 'Y-m-d');
    }

    /**
     * @param $value
     * @return int
     * @throws Exception
     */
    private static function formatTimestamp($value): int
    {
        return (int)self::dateFormatter($value, 'U');
    }

    private static function formatBooleanAsString($value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
    }
}