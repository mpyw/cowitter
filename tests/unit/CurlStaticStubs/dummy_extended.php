<?php

namespace mpyw\TestOfCowitter;

class Line implements \IteratorAggregate
{
    private $parts;
    private $trailer;

    public static function wrap(array $block, $trailer)
    {
        $r = [];
        foreach ($block as $line) {
            if (!$line instanceof self) {
                if (is_array($line)) {
                    $line = new self($line, $trailer);
                } else {
                    $line = new self([$line], $trailer);
                }
            }
            $r[] = $line;
        }
        return $r;
    }

    public function __construct(array $parts, $trailer)
    {
        $this->parts = $parts;
        $this->trailer = $trailer;
    }

    public function __toString()
    {
        return implode($this->parts) . $this->trailer;
    }

    public function getIterator()
    {
        $max = count($this->parts) - 1;
        foreach ($this->parts as $i => $v) {
            yield $i => $i === $max ? "$v$this->trailer" : $v;
        }
    }
}

class CurlFaker
{
    private static $data = [];

    public static function set($url, array $lines)
    {
        self::$data[$url] = $lines;
    }

    public static function clear() {
        self::$data = [];
    }

    public static function get($url)
    {
        return self::$data[$url] ?? null;
    }
}

function curl_flush_content(\stdClass $ch) {

    if ($ch->options[CURLOPT_URL] === null) {
        $ch->errno = CURLE_URL_MALFORMAT;
        $ch->error = 'No URL set!';
        return false;
    }
    $fake = CurlFaker::get($ch->options[CURLOPT_URL]);
    if ($fake === null) {
        $ch->errno = CURLE_COULDNT_CONNECT;
        $ch->error = 'Could not connect';
        return false;
    }

    list($header, $body) = $fake;
    $header[] = '';
    $body = Line::wrap($body, "\n");

    $ch->info['http_code'] = intval(explode(' ', $header[0])[1]);
    $ch->info['header_size'] = strlen(implode("\r\n", $header) . "\r\n");
    $ch->info['url'] = $ch->options[CURLOPT_URL];
    $ch->info['content_type'] =
        preg_match('/^Content-Type:\s*(.+?)\r\n/mi', implode("\r\n", $header) . "\r\n", $m)
        ? $m[1] : 'application/json; charset=UTF-8';
    $ch->content = implode([
        implode("\r\n", $header) . "\r\n",
        implode(array_map('strval', $body)),
    ]);

    if (!empty($ch->options[CURLOPT_HEADERFUNCTION]) || !empty($ch->options[CURLOPT_HEADERFUNCTION])) {

        if (!empty($ch->options[CURLOPT_HEADERFUNCTION])) {
            $callback = $ch->options[CURLOPT_HEADERFUNCTION];
            foreach ($header as $buffer) {
                $buffer .= "\r\n";
                if (strlen($buffer) !== $callback($ch, $buffer)) {
                    $ch->errno = CURLE_WRITE_ERROR;
                    $ch->error = 'Failed writing header';
                    return false;
                }
            }
        }

        if (!empty($ch->options[CURLOPT_WRITEFUNCTION])) {
            $callback = $ch->options[CURLOPT_WRITEFUNCTION];
            foreach ($body as $line) {
                foreach ($line as $buffer) {
                    if (strlen($buffer) !== $callback($ch, $buffer)) {
                        $ch->errno = CURLE_WRITE_ERROR;
                        $ch->error = 'Failed writing body';
                        return false;
                    }
                }
            }
        }

        return true;

    } elseif (!empty($ch->options[CURLOPT_RETURNTRANSFER])) {

        return $ch->content;

    } else {

        return true;

    }

}
