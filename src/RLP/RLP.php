<?php

namespace nutterz2009\RLP;

use nutterz2009\SharedTrait;

class RLP
{
    use SharedTrait;

    public function encode($data): string
    {
        $encoded = $this->rlpEncode($data);

        return bin2hex($encoded);
    }

    protected function rlpEncode($data): string
    {
        if (is_string($data)) {
            $data = hex2bin($this->stripHexPrefix($data));
            if (ord($data) === 0) {
                $data = '';
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

    protected function encodeLength(int $length, int $offset): string
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

    protected function toBinary(int $x): string
    {
        if ($x == 0) {
            return '';
        } else {
            return $this->toBinary(gmp_strval(gmp_div($x, 256))) . chr(gmp_strval(gmp_mod($x, 256)));
        }
    }

    public function decode(string $input): array
    {
        if (hexdec(substr($input, 0, 2)) < 0x7f) {
            // if is newer transaction type, the first hex value will be the tx type
            $input = substr($input, 2);
        }

        return $this->decodeRLP(hex2bin($input))[0];
    }

    protected function decodeRLP(string $input): array
    {
        $output = [];
        while (strlen($input) !== 0) {
            list($offset, $dataLen, $type) = $this->decodeLength($input);
            if (is_string($type)) {
                $output[] = bin2hex(substr($input, $offset, $dataLen));
            } elseif (is_array($type)) {
                $output[] = $this->decodeRLP(substr($input, $offset, $dataLen));
            } else {
                throw new \InvalidArgumentException('Invalid RLP');
            }
            $input = substr($input, $offset + $dataLen);
        }

        return $output;
    }

    protected function decodeLength(string $input): array
    {
        $length = strlen($input);
        if ($length == 0) {
            throw new \InvalidArgumentException('Input is null');
        }
        $prefix = ord(substr($input, 0, 1));
        if ($prefix <= 0x7f) {
            return [0, 1, 'string'];
        } elseif ($prefix <= 0xb7 && $length > $prefix - 0x80) {
            $strLen = $prefix - 0x80;

            return [1, $strLen, 'string'];
        } elseif ($prefix <= 0xbf && $length > $prefix - 0xb7 && $length > $prefix - 0xb7 + $this->toInteger(substr($input, 1, $prefix - 0xb7))) {
            $lenOfStrLen = $prefix - 0xb7;
            $strLen = $this->toInteger(substr($input, 1, $lenOfStrLen));

            return [1+$lenOfStrLen, $strLen, 'string'];
        } elseif ($prefix <= 0xf7 && $length > $prefix - 0xc0) {
            $listLen = $prefix - 0xc0;

            return [1, $listLen, array()];
        }  elseif ($prefix <= 0xff && $length > $prefix - 0xf7 && $length > $prefix - 0xf7 + $this->toInteger(substr($input, 1, $prefix - 0xf7))) {
            $lenOfListLen = $prefix - 0xf7;
            $listLen = $this->toInteger(substr($input, 1, $lenOfListLen));

            return [1 + $lenOfListLen, $listLen, array()];
        } else {
            throw new \InvalidArgumentException('Input doesn\'t conform with RLP encoding standard.');
        }
    }

    protected function toInteger($b): int
    {
        $length = strlen($b);
        if ($length === 0) {
            throw new \InvalidArgumentException('Input is null.');
        } else if ($length === 1) {
            return ord(substr($b, 0, 1));
        } else {
            return gmp_intval(gmp_add(ord(substr($b, -1)), gmp_mul($this->toInteger(substr($b, 0, -1)), 256)));
        }
    }
}
