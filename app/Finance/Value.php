<?php

namespace App\Finance;

/**
 * Typed Firestore values, and the special values the client needs:
 * server timestamps, increments, explicit maps and timestamps.
 */
class Value
{
    private $kind;
    private $value;

    private function __construct($kind, $value = null)
    {
        $this->kind = $kind;
        $this->value = $value;
    }

    /** serverTimestamp(): set by Firestore at commit time. */
    public static function serverTimestamp()
    {
        return new self('serverTimestamp');
    }

    /** increment(n): adds n to the stored number at commit time. */
    public static function increment($n)
    {
        return new self('increment', $n);
    }

    /** An explicit map, needed only to write an empty one. */
    public static function map(array $fields)
    {
        return new self('map', $fields);
    }

    public static function timestamp(\DateTimeInterface $at)
    {
        return new self('timestamp', $at);
    }

    public function isTransform()
    {
        return $this->kind === 'serverTimestamp' || $this->kind === 'increment';
    }

    public function transform($fieldPath)
    {
        if ($this->kind === 'serverTimestamp') {
            return ['fieldPath' => $fieldPath, 'setToServerValue' => 'REQUEST_TIME'];
        }
        return ['fieldPath' => $fieldPath, 'increment' => self::encode($this->value)];
    }

    public static function isMap(array $a)
    {
        return $a !== [] && array_keys($a) !== range(0, count($a) - 1);
    }

    public static function encodeFields(array $fields)
    {
        $out = [];
        foreach ($fields as $k => $v) {
            $out[(string)$k] = self::encode($v);
        }
        return (object)$out;
    }

    public static function encode($v)
    {
        if ($v instanceof self) {
            if ($v->kind === 'map') {
                return ['mapValue' => ['fields' => self::encodeFields($v->value)]];
            }
            if ($v->kind === 'timestamp') {
                return self::encode($v->value);
            }
            throw new \LogicException('A server value cannot be nested in an array or used in a query.');
        }
        if ($v === null) {
            return ['nullValue' => null];
        }
        if (is_bool($v)) {
            return ['booleanValue' => $v];
        }
        if (is_int($v)) {
            return ['integerValue' => (string)$v];
        }
        if (is_float($v)) {
            return ['doubleValue' => $v];
        }
        if (is_string($v)) {
            return ['stringValue' => $v];
        }
        if ($v instanceof \DateTimeInterface) {
            $utc =\DateTimeImmutable::createFromFormat('U.u', $v->format('U.u'))->setTimezone(new \DateTimeZone('UTC'));
            return ['timestampValue' => $utc->format('Y-m-d\TH:i:s.u\Z')];
        }
        if (is_array($v)) {
            if (self::isMap($v)) {
                return ['mapValue' => ['fields' => self::encodeFields($v)]];
            }
            return ['arrayValue' => $v ? ['values' => array_map([self::class, 'encode'], array_values($v))] : (object)[]];
        }
        throw new \InvalidArgumentException('Cannot store a value of type ' . gettype($v));
    }

    public static function decodeFields($fields)
    {
        $out = [];
        foreach ($fields as $k => $v) {
            $out[$k] = self::decode($v);
        }
        return $out;
    }

    public static function decode(array $v)
    {
        if (array_key_exists('integerValue', $v)) {
            return (int)$v['integerValue'];
        }
        if (array_key_exists('doubleValue', $v)) {
            return (float)$v['doubleValue'];
        }
        if (array_key_exists('stringValue', $v)) {
            return $v['stringValue'];
        }
        if (array_key_exists('booleanValue', $v)) {
            return (bool)$v['booleanValue'];
        }
        if (array_key_exists('nullValue', $v)) {
            return null;
        }
        if (array_key_exists('timestampValue', $v)) {
            return new \DateTimeImmutable($v['timestampValue']);
        }
        if (array_key_exists('mapValue', $v)) {
            return self::decodeFields($v['mapValue']['fields'] ?? []);
        }
        if (array_key_exists('arrayValue', $v)) {
            $array = (array)$v['arrayValue'];
            return array_map([self::class, 'decode'], $array['values'] ?? []);
        }
        if (array_key_exists('referenceValue', $v)) {
            return $v['referenceValue'];
        }
        if (array_key_exists('geoPointValue', $v)) {
            return $v['geoPointValue'];
        }
        if (array_key_exists('bytesValue', $v)) {
            return $v['bytesValue'];
        }
        return null;
    }

    /** Plain JSON-friendly copy (timestamps as ISO strings), for the audit log and the screens. */
    public static function plain($v)
    {
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d\TH:i:s.v\Z');
        }
        if ($v instanceof self) {
            return $v->kind === 'serverTimestamp' ? '(server time)' : ($v->kind === 'increment' ? '(+' . $v->value . ')' : self::plain($v->value));
        }
        if (is_array($v)) {
            return array_map([self::class, 'plain'], $v);
        }
        return $v;
    }
}
