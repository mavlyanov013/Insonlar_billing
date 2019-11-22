<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace common\components;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use NumberFormatter;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\db\Expression;

class Formatter extends \yii\i18n\Formatter
{
    private $_intlLoaded = false;

    public function init()
    {
        $this->_intlLoaded = extension_loaded('intl');
        parent::init();
    }

    public function asCurrency($value, $currency = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded) {
            $formatter = $this->createNumberFormatter(NumberFormatter::CURRENCY, null, $options, $textOptions);
            if ($currency === null) {
                if ($this->currencyCode === null) {
                    if (($result = $formatter->format($value)) === false) {
                        throw new InvalidParamException('Formatting currency value failed: ' . $formatter->getErrorCode() . ' ' . $formatter->getErrorMessage());
                    }
                    return $result;
                }
                $currency = $this->currencyCode;
            }

            //correct displaying currency in uzbek language
            if ($currency == 'UZS' && $formatter->getLocale() == 'uz') {
                $formatter->setPattern('#,##0 so‘m');
            }
            if ($currency == 'UZS' && $formatter->getLocale() == 'ru') {
                $formatter->setPattern('#,##0 сум');
            }
            if (($result = $formatter->formatCurrency($value, $currency)) === false) {
                throw new InvalidParamException('Formatting currency value failed: ' . $formatter->getErrorCode() . ' ' . $formatter->getErrorMessage());
            }
            return $result;
        } else {
            if ($currency === null) {
                if ($this->currencyCode === null) {
                    throw new InvalidConfigException('The default currency code for the formatter is not defined and the php intl extension is not installed which could take the default currency from the locale.');
                }
                $currency = $this->currencyCode;
            }
            return $currency . ' ' . $this->asDecimal($value, 2, $options, $textOptions);
        }
    }

    protected function normalizeDatetimeValue($value, $checkDateTimeInfo = false)
    {
        // checking for DateTime and DateTimeInterface is not redundant, DateTimeInterface is only in PHP>5.5
        if ($value === null || $value instanceof DateTime || $value instanceof DateTimeInterface) {
            // skip any processing
            return $checkDateTimeInfo ? [$value, true, true] : $value;
        }
        if ($value instanceof Expression) {
            // skip any processing
            return new DateTime('now', new DateTimeZone('UTC'));
        }
        if (empty($value)) {
            $value = 0;
        }
        try {
            if (is_numeric($value)) { // process as unix timestamp, which is always in UTC
                $timestamp = new DateTime('@' . (int)$value, new DateTimeZone('UTC'));
                return $checkDateTimeInfo ? [$timestamp, true, true] : $timestamp;
            } elseif (($timestamp = DateTime::createFromFormat('Y-m-d', $value, new DateTimeZone($this->defaultTimeZone))) !== false) { // try Y-m-d format (support invalid dates like 2012-13-01)
                return $checkDateTimeInfo ? [$timestamp, false, true] : $timestamp;
            } elseif (($timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $value, new DateTimeZone($this->defaultTimeZone))) !== false) { // try Y-m-d H:i:s format (support invalid dates like 2012-13-01 12:63:12)
                return $checkDateTimeInfo ? [$timestamp, true, true] : $timestamp;
            }
            // finally try to create a DateTime object with the value
            if ($checkDateTimeInfo) {
                $timestamp = new DateTime($value, new DateTimeZone($this->defaultTimeZone));
                $info = date_parse($value);
                return [
                    $timestamp,
                    !($info['hour'] === false && $info['minute'] === false && $info['second'] === false),
                    !($info['year'] === false && $info['month'] === false && $info['day'] === false)
                ];
            } else {
                return new DateTime($value, new DateTimeZone($this->defaultTimeZone));
            }
        } catch (\Exception $e) {
            throw new InvalidParamException("'$value' is not a valid date time value: " . $e->getMessage()
                                            . "\n" . print_r(DateTime::getLastErrors(), true), $e->getCode(), $e);
        }
    }

}
