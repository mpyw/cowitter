<?php

namespace mpyw\Cowitter\Helpers;

class UrlNormalizer
{
    protected static $specialUrls = [
        'i/activity/about_me' =>
            'https://api.twitter.com/i/activity/about_me.json',
        'i/activity/by_friends' =>
            'https://api.twitter.com/i/activity/by_friends.json',
        'site' =>
            'https://sitestream.twitter.com/1.1/site.json',
        'statuses/filter' =>
            'https://stream.twitter.com/1.1/statuses/filter.json',
        'statuses/firehose' =>
            'https://stream.twitter.com/1.1/statuses/firehose.json',
        'statuses/sample' =>
            'https://stream.twitter.com/1.1/statuses/sample.json',
        'media/upload' =>
            'https://upload.twitter.com/1.1/media/upload.json',
        'user' =>
            'https://userstream.twitter.com/1.1/user.json',
    ];

    protected static $versions = ['1.1' => true, '1' => true, 'i' => true];

    protected static function twitterFixPathSegments(array $segments)
    {
        if (!$segments) {
            return $segments;
        }
        if (!isset(static::$versions[$segments[0]])) {
            array_unshift($segments, '1.1');
        }
        if (count($segments) > 1 && !preg_match('/[.:]/', end($segments))) {
            $segments[] = array_pop($segments) . '.json';
        }
        return $segments;
    }

    protected static function twitterBuildUrl(array $e, array $segments)
    {
        return (isset($e['scheme']) ? $e['scheme'] : 'https')
            . '://'
            . (isset($e['host']) ? $e['host'] : 'api.twitter.com')
            . (isset($e['port']) ? ':' . $e['port'] : '')
            . '/'
            . implode('/', $segments)
        ;
    }

    public static function twitterSplitUrlAndParameters($endpoint)
    {
        if (isset(static::$specialUrls[$endpoint])) {
            return [static::$specialUrls[$endpoint], []];
        }
        if (false === $e = parse_url($endpoint)) {
            throw new \DomainException('Invalid URL.');
        }
        $segments = preg_split('@/++@', isset($e['path']) ? $e['path'] : '', -1, PREG_SPLIT_NO_EMPTY);
        if (!isset($e['host'])) {
            $segments = static::twitterFixPathSegments($segments);
        }
        parse_str(isset($e['query']) ? $e['query'] : '', $params);
        return [static::twitterBuildUrl($e, $segments), $params];
    }

    protected static function outBuildUrl(array $e, array $segments)
    {
        if (!isset($e['host'])) {
            throw new \DomainException('Invalid URL: Missing host.');
        }
        return (isset($e['scheme']) ? $e['scheme'] : 'https')
            . '://'
            . (isset($e['user']) ? $e['user'] . (isset($e['pass']) ? ':' . $e['pass'] : '') . '@' : '')
            . $e['host']
            . (isset($e['port']) ? ':' . $e['port'] : '')
            . '/'
            . implode('/', $segments)
        ;
    }

    public static function outSplitUrlAndParameters($endpoint)
    {
        if (false === $e = parse_url($endpoint)) {
            throw new \DomainException('Invalid URL.');
        }
        $segments = preg_split('@/++@', isset($e['path']) ? $e['path'] : '', -1, PREG_SPLIT_NO_EMPTY);
        parse_str(isset($e['query']) ? $e['query'] : '', $params);
        return [static::outBuildUrl($e, $segments), $params];
    }
}
