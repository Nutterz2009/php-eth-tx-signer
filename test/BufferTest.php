<?php

namespace nutterz2009;

use nutterz2009\Encoder\Buffer;
use PHPUnit\Framework\TestCase;

class BufferTest extends TestCase {

    /**
     * @dataProvider input
     * @param $expect
     * @param $data
     */
    public function testStringConcat($expect, $data) {
        $Buffer = new Buffer();

        $Buffer->concat($data);

        $this->assertSame($expect, (string) $Buffer);
    }

    public static function input (): array {
        return [
            [
                '05f4d3c2b1a0',
                '5f4d3c2b1a0'
            ],
            [
                '05f4d3c2b1a0',
                Buffer::fromHexArray(['5', 'f4', 'd3', 'c2', 'b1', 'a0'])
            ],
            [
                '0f7bf200003701',
                Buffer::fromArray([15, 123, 242, 0, 0, 55, 1])
            ],
            [
                '0f7bf200003701',
                Buffer::fromArray([15, 123])->concat('f200003701'),
            ],
            [
                '62756666657253686f756c64576f726b',
                (new Buffer())->concat('bufferShouldWork', Buffer::ENCODING_ASCII)
            ],
            [
                '62756666657253686f756c64576f726b',
                (new Buffer())
                    ->concat('627566666572', Buffer::ENCODING_HEX)
                    ->concat('Should', Buffer::ENCODING_ASCII)
                    ->concat(ord('W'), Buffer::ENCODING_INT)
                    ->concat(ord('o'), Buffer::ENCODING_INT)
                    ->concat(ord('r'), Buffer::ENCODING_INT)
                    ->concat(ord('k'), Buffer::ENCODING_INT)
            ],
        ];
    }

    /**
     * @dataProvider arrayInput
     * @param $expect
     * @param $data
     */
    public function testByteConcat($expect, $data) {
        $Buffer = new Buffer();

        $Buffer->concat($data);

        $this->assertSame($expect, $Buffer->getArray());
    }

    public static function arrayInput (): array {
        return [
            [
                [5, 244, 211, 194, 177, 160],
                '5f4d3c2b1a0'
            ],
            [
                [5, 244, 211, 194, 177, 160],
                Buffer::fromHexArray(['5','f4','d3','c2','b1','a0'])
            ],
            [
                [15, 123, 242, 0, 0, 55, 1],
                Buffer::fromArray([15, 123, 242, 0, 0, 55, 1])
            ],
            [
                [98, 117, 102, 102, 101, 114, 83, 104, 111, 117, 108, 100, 87, 111, 114, 107],
                (new Buffer())->concat('bufferShouldWork', Buffer::ENCODING_ASCII)
            ],
        ];
    }
}
