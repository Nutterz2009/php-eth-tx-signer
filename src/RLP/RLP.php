<?php

namespace nutterz2009\RLP;

use nutterz2009\Encoder\Buffer;

class RLP
{
    private function compact($data)
    {
        if (is_array($data)) {
            $out = '';
            foreach ($data as $item) {
                if (is_array($item)) {
                    $out .= $this->compact($item);
                } else {
                    $out .= $item;
                }
            }

            return $out;
        }

        return $data;
    }

    public function encode($data)
    {
        $data = $this->encodeToByteString($data);
        $decoded = $this->decodeFromByteString($this->compact($data));

        $encoded = $this->rlpEncode($data);

        return $this->decodeFromByteString($encoded);
    }

    private function encodeToByteString($data)
    {
        if (is_array($data)) {
            $out = [];
            foreach ($data as $datum) {
                $out[] = $this->encodeToByteString($datum);
            }

            return $out;
        } else {
            if (strcasecmp("0x", substr($data, 0, 2)) === 0) {
                $data = substr($data, 2, strlen($data) - 2);
            }
            $data = $this->hexify($data);
            $charString = '';
            for ($i = 0; $i < strlen($data); $i += 2) {
                $charString .= chr(hexdec(substr($data, $i, 2)));
            }

            return $charString;
        }
    }

    private function decodeFromByteString(string $data): string
    {
        $out = '';
        while(strlen($data) !== 0) {
            $out .= $this->hexify(dechex(ord(substr($data, 0, 1))));

            $data = substr($data, 1, strlen($data) - 1);
        }

        return $out;
    }

    public function rlpEncode($data)
    {
        if (is_string($data)) {
            if (ord($data) === 0) {
                $data = ''; // This is needed because of the way PHP handles numbers
            }

            if (strlen($data) === 1 && ord($data) < 0x80) {
                return $data;
            } else {
                return $this->encodeLength(strlen($data), 0x80) . $data;
            }
        } else if (is_array($data)) {
            $output = '';
            foreach ($data as $item) {
                $output .= $this->rlpEncode($item);
            }

            return $this->encodeLength(strlen($output), 0xc0) . $output;
        } else {
            throw new \RuntimeException('Invalid data provided.');
        }
    }

    private function encodeLength(int $length, int $offset)
    {
        if ($length < 56) {
            return chr($length + $offset);
        } elseif (gmp_cmp($length, gmp_pow(256, 8)) < 0) {
            $bl = $this->toBinary($length);

            return chr(strlen($bl) + $offset + 55) . $bl;
        } else {
            throw new \RuntimeException('Data too long (TWSS).');
        }
    }

    private function toBinary(int $x)
    {
        if ($x == 0) {
            return '';
        } else {
            return $this->toBinary(gmp_strval(gmp_div($x, 256))) . chr(gmp_strval(gmp_mod($x, 256)));
        }
    }

    private function hexify(string $hex)
    {
        if (strlen($hex) % 2 === 1) {
            return '0' . $hex;
        }

        return $hex;
    }

    public function decode($input)
    {
        if ($input instanceof Buffer) {
            $buffer = $input;
        } else {
            $buffer = new Buffer;

            $buffer->concat($input, Buffer::ENCODING_HEX);
        }

        if (!count($buffer)) {
            return [];
        }

        if ($buffer[0] === 2) {
            $buffer = $buffer->getSlice(1);
        }

        $output = [];
        list($offset, $len, $type) = $this->decodeLength($buffer);
        if (is_string($type)) {
            $output[] = $buffer->getSlice($offset, $len)->__toString();
        } else if (is_array($type)) {
            $output = array_merge($output, $this->decode($buffer->getSlice($offset, $len)));
        }  else {
            throw new \RuntimeException("Should not happen");
        }

        $start = $offset + $len;

        $second = $this->decode($buffer->getSlice($start));

        if ($start !== $buffer->count()) {
            $output = array_merge($output, $second);
        }

        return $output;
    }

    protected function decodeLength(Buffer $buffer)
    {
        $len = count($buffer);

        if ($len === 0) {
            throw new \RuntimeException('Input is null');
        }

        $prefix = $buffer[0];
        if ($prefix <= 0x7f) {
            return [0, 1, 'str'];
        } else if ($prefix <= 0xb7 && $len > ($prefix) - 0x80) {
            return [1, $prefix - 0x80, 'str'];
        } else if ($prefix <= 0xbf && $len > $prefix - 0xb7 && $len > $prefix - 0xb7 + $this->toInteger($buffer->getSlice(1, $prefix - 0xb7))) {
            $strLen = $prefix - 0xb7;

            return [1 + $strLen, $this->toInteger($buffer->getSlice(1, $strLen)), 'str'];
        } else if ($prefix <= 0xf7 && $len > $prefix - 0xc0) {
            return [1, $prefix - 0xc0, []];
        } else if ($prefix <= 0xff && $len > $prefix - 0xf7 && $len > $prefix - 0xf7 + $this->toInteger($buffer->getSlice(1, $prefix - 0xf7))) {
            $strLen = $prefix - 0xf7;
            $listLen = $this->toInteger($buffer->getSlice(1, $strLen));

            return [1 + $strLen, $listLen, []];
        } else {
            throw new \RuntimeException('Input does not conform with RLP encoding standard.');
        }
    }

    protected function toInteger(Buffer $buffer)
    {
        $length = $buffer->count();

        if ($length === 0) {
            throw new \RuntimeException('Empty buffer supplied.');
        } else if ($length === 1) {
            return $buffer[0];
        } else {
            return $buffer->getSlice(-1)[0] + $this->toInteger($buffer->getSlice(0, -1)) * 256;
        }
    }

    protected function checkHexLead($val)
    {
        if (((int) $val) % 2 === 0) {
            return $val;
        }

        return '0' . $val;
    }

    protected function stripHex(string $val): string
    {
        if (substr($val, 0, 2) === '0x') {
            return substr($val, 2, strlen($val) - 2) ?? '';
        }

        return $val;
    }
}
