<?php

namespace nutterz2009\Encoder;

class Buffer implements \ArrayAccess, \Countable
{
    const ENCODING_ASCII = 'ascii';
    const ENCODING_HEX = 'hex';
    const ENCODING_INT = 'int';
    const ENCODING_BUFFER = 'buffer';

    /** @var array */ // Byte array
    protected $data = [];

    public function concat($input, string $encoding = Buffer::ENCODING_HEX): Buffer
    {
        switch ($encoding) {
            case static::ENCODING_ASCII:
                $this->data = array_merge($this->data, $this->asciiStringToBinary($input));

                break;
            case static::ENCODING_HEX:
                if (strlen($input) > 1 && in_array(substr($input, 0, 2), ['0x', '0X'])) {
                    $input = substr($input, 2, strlen($input) - 2);
                } else if(!strlen($input)) {
                    $input = dechex(128);
                }

                $this->data = array_merge($this->data, $this->byteify($input));

                break;
            case static::ENCODING_BUFFER:
                if (!($input instanceof Buffer)) {
                    throw new \RuntimeException('Input should be type Buffer.');
                }

                $this->data = array_merge($this->data, $input->getArray());

                break;
            case static::ENCODING_INT:
                if ($input < 0 || $input > 255) {
                    throw new \RuntimeException('Input is not a valid byte.');
                }

                $this->data[] = $input;

                break;

            default:
                throw new \RuntimeException('Invalid encoding supplied.');
        }

        return $this;
    }

    private function asciiStringToBinary(string $input): array
    {
        $out = [];
        while(strlen($input) > 0) {
            $out[] = ord(substr($input, 0, 1));

            $input = substr($input, 1, strlen($input) - 1);
        }

        return $out;
    }

    public function getSlice(int $start, ?int $length = null)
    {
        return static::fromArray(array_slice($this->data, $start, $length));
    }

    public static function fromArray(array $byteArray)
    {
        $buffer = new Buffer();

        $buffer->data = $byteArray;

        return $buffer;
    }

    public static function fromHexArray(array $hexArray)
    {
        $buffer = new Buffer();

        foreach ($hexArray as $item) {
            $buffer->data[] = hexdec($item);
        }

        return $buffer;
    }

    public function getInt()
    {
        $hex = '';
        foreach ($this->data as $datum) {
            $hex .= $this->hexify($datum);
        }

        return hexdec($hex);
    }

    // returns byte array of the supplied hex string
    private function byteify(string $str): array
    {
        $this->checkHexString($str);

        $str = $this->hexify($str);

        $out = [];
        while(strlen($str) > 0) {
            $out[] = hexdec(substr($str, 0, 2));

            $str = substr($str, 2, strlen($str) - 2);
        }

        return $out;
    }

    // ensures that the hex string has an even length for processing
    private function hexify(string $hex)
    {
        if (strlen($hex) % 2 === 1) {
            return '0' . $hex;
        }

        return $hex;
    }

    public function __toString()
    {
        $out = '';
        foreach ($this->data as $datum) {
            $out .= $this->hexify(dechex($datum));
        }

        return $out;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function toHexArray()
    {
        $out = [];
        foreach ($this->data as $datum) {
            $out[] = dechex($datum);
        }

        return $out;
    }

    public function getArray()
    {
        return $this->data;
    }

    public function count()
    {
        return count($this->data);
    }

    protected function checkHexString(string $hexString)
    {
        if (preg_match('/^[a-fA-F0-9]+$/', $hexString) !== 1) {
            print_r("Invalidated String: '{$hexString}'");
            throw new \RuntimeException('ByteBuffer Invalid hex string.');
        }
    }
}