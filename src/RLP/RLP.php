<?php

namespace nutterz2009\RLP;

use nutterz2009\Encoder\Buffer;

class RLP
{
    public function encode($data)
    {
        return (string) $this->rlpEncode($data);
    }

    public function rlpEncode($data): Buffer
    {
        if (is_numeric($data)) {
            $data = '0x' . (string) dechex((int)$data);
        }

        if (is_string($data)) {
            $buffer = new Buffer();
            $buffer->concat(strtolower($this->stripHex($data)), Buffer::ENCODING_HEX);

            if ($buffer->count() === 1 && hexdec($buffer) === 0x80) {
                return $buffer;
            } else if (count($buffer) < 2 && hexdec($buffer) < 0x80) { // changed from ord($buffer)
                if (!count($buffer)) {
                    $buffer->concat('80', Buffer::ENCODING_HEX);
                }

                return $buffer;
            } else {
                $encodedLength = $this->encodeLength(count($buffer), 0x80);

                $outBuffer = new Buffer();
                $outBuffer->concat($encodedLength, Buffer::ENCODING_BUFFER);

                return $outBuffer->concat($buffer, Buffer::ENCODING_BUFFER);
            }
        } else if (is_array($data)) {
            $output = new Buffer;
            foreach ($data as $datum) {
                $output->concat($this->rlpEncode($datum), Buffer::ENCODING_BUFFER);
            }

            $out = new Buffer();

            $out->concat($this->encodeLength(count($output), 0xC0), Buffer::ENCODING_BUFFER);

            return $out->concat($output, Buffer::ENCODING_BUFFER);;
        } else {
            throw new \RuntimeException('Invalid data provided.');
        }
    }

    private function encodeLength(int $len, int $offset)
    {
        if ($len < 56) {
            $buffer = new Buffer();

            return $buffer->concat(dechex($len + $offset), Buffer::ENCODING_HEX);
        } else if ($len < (256 ** 8)) {
            $buffer = new Buffer();
            $buffer->concat($this->toBinary($len), Buffer::ENCODING_HEX);

            $outBuffer = new Buffer;
            $outBuffer->concat(dechex(count($buffer) + $offset + 55), Buffer::ENCODING_HEX);

            return $outBuffer->concat($buffer, Buffer::ENCODING_HEX);
        } else {
            throw new \RuntimeException('Invalid data provided.');
        }
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

    protected function toBinary($val): Buffer
    {
        if  ($val === 0) {
            return new Buffer();
        } else {
            $buffer = new Buffer();

            return $buffer->concat($this->toBinary((int) ($val / 256)), Buffer::ENCODING_BUFFER)
                ->concat(dechex($val % 256), Buffer::ENCODING_HEX);
        }
    }

    protected function checkHexLead($val)
    {
        if (((int) $val) % 2 === 0) {
            return $val;
        }

        return '0' . $val;
    }

    protected function stripHex(string $val)
    {
        if (substr($val, 0, 2) === '0x') {
            return substr($val, 2, strlen($val) - 2);
        }

        return $val;
    }
}
