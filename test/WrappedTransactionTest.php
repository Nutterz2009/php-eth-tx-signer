<?php

namespace nutterz2009;

use nutterz2009\Ethereum\WrappedTransaction;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Web3p\RLP\RLP;

class WrappedTransactionTest extends TestCase {

    /**
     * @dataProvider input
     * @param $expect
     * @param $type
     * @param $chainId
     * @param $nonce
     * @param $gasPrice
     * @param $gasLimit
     * @param $to
     * @param $value
     * @param $data
     * @param $accessList
     */
    public function testGetInput ($expect, $type, $chainId, $nonce, $gasPrice, $gasLimit, $to, $value, $data, $accessList) {
        $transaction = new WrappedTransaction ($type, $chainId, $nonce, $gasPrice, $gasLimit, $to, $value, $data, $accessList);

        $this->assertSame($expect, $transaction->getInput());
    }

    public static function input (): array {
        return [
            [
                ['chainId' => '', 'nonce' => '', 'maxPriorityFeePerGas' => '', 'maxFeePerGas' => '', 'gasLimit' => '', 'to' => '', 'value' => '', 'data' => '', 'accessList' => [], 'y' => '', 'r' => '', 's' => ''],
                '', '', '', '', '', '', '', '', '', '', []
            ],
            [
                ['chainId' => '1', 'nonce' => '04', 'maxPriorityFeePerGas' => 'a21fe80', 'maxFeePerGas' => '03f5476a00', 'gasLimit' => '027f4b', 'to' => '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', 'value' => '2a45907d1bef7c00', 'data' => '', 'accessList' => [], 'y' => '', 'r' => '', 's' => ''],
                '2', '1', '04', 'a21fe80', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', '', []
            ],
        ];
    }

    /**
     * @dataProvider getRaw
     * @param $expect
     * @param $privateKey
     * @param $type
     * @param $chainId
     * @param $nonce
     * @param $maxPriorityFeePerGas
     * @param $maxFeePerGas
     * @param $gasLimit
     * @param $to
     * @param $value
     * @param $data
     * @param $accessList
     */
    public function testGetRaw ($expect, $privateKey, $type, $chainId, $nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data, $accessList) {
        $transaction = new WrappedTransaction($type, $chainId, $nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data, $accessList);
        $raw = $transaction->getRaw($privateKey);
        $this->assertSame($expect, $raw, $raw);
    }

    /**
     * @dataProvider getRaw
     * @param $expect
     * @param $privateKey
     * @param $type
     * @param $chainId
     * @param $nonce
     * @param $maxPriorityFeePerGas
     * @param $maxFeePerGas
     * @param $gasLimit
     * @param $to
     * @param $value
     * @param $data
     * @param $accessList
     */
    public function testDecodeRaw ($expect, $privateKey, $type, $chainId, $nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data, $accessList) {

        $rlp = new RLP();
        $decoded = $rlp->decode($expect);

        $transaction = new WrappedTransaction($type, $chainId, $nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data, $accessList);

        $tx = $transaction->getRaw($privateKey);
        $decoded2 = $rlp->decode($tx);
        print_r($decoded);
        print_r($decoded2);
        $this->assertSame($decoded, $decoded2, $tx);
        print_r($tx);


        $this->assertSame($decoded[0], $this->hexify($chainId));
        $this->assertSame($decoded[1], $this->hexify($nonce));
        $this->assertSame($decoded[2], $this->hexify($maxPriorityFeePerGas));
        $this->assertSame($decoded[3], $this->hexify($maxFeePerGas));
        $this->assertSame($decoded[4], $this->hexify($gasLimit));
        $this->assertSame($decoded[5], $this->hexify($to));
        $this->assertSame($decoded[6], $this->hexify($value));
        $this->assertSame($decoded[7], $this->hexify($data));

        $this->assertSame($expect, $tx);
    }

    public static function getRaw (): array {
        return [
            [
                '02f87501808503f5476a008503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c0080c080a0c77e00e2a7da7db2da606fa4128c3a97d1fd87a3d54e6a88fbecbbd786296d49a04a9201a7f2e07b05cac22a5e4c7f6166fdc33d03decc7de6ef848464a02e1d3c',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 2, 1, '0', '03f5476a00', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', '', []
            ],
            [
                '02f8760181808503f5476a008503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c0080c001a071517e0d94fbebd3b21c40236e1ee849ba0b9f785c9095a46b7feda8546d01d1a05096f06c39998a768c8fae474ee5afa312aba9d09628bbff021d53bce80d7707',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 2, 1, '80', '03f5476a00', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', '', []
            ],
            [
                '02f87501018503f5476a008503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c0080c001a0ae0d4f6a02b836a8d1d199a4a7072720aa0b2b0bd62c3f7aa7282360e20cf4c7a039bfeb0ac13cc527ca6eda4431ddd2cdee70ade2acb0066f7096298eb6c7dd22',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 2, 1, '01', '03f5476a00', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', '', []
            ],
            [//                                                                                                       ||
                '02f87501048503f5476a008503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c0080c080a05249f427fe1ccd2ed5404ccd65a58def6f905e19fcfb95740e56a67c842824d9a05343a214cf93b65bc195ba0db879e972c6cc146b6775ac3c41c2618640d2c68b',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 2, 1, '4', '03f5476a00', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', '', []
            ],
            [
                '02f87503048503f5476a008503f5476a0083027f4b942d1b28bb956a25f98133ca797a993a14fddbec8088043a280a6a5a0c0080c080a0ac30fc461706cef108323afa85cc6766869b2eaee94923dc22f5b5774b82e8e3a05fd6ece6df0eba29a5d498d608f70dd6d7a366d8192887a69236f9f44b723040',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 2, 3, '04', '03f5476a00', '03f5476a00', '027f4b', '2d1b28bb956a25f98133ca797a993a14fddbec80', '43a280a6a5a0c00', '', []
            ],
            [
                '02f8740306846553f1008503f5476a0083027f4b942d1b28bb956a25f98133ca797a993a14fddbec8088016345785d8a000080c080a0d0c5504fc08c5cfa5ed78b86ca96f02df9959e8aeb7bf0c7d8e9844f61dbc469a042c4503ae4141a06905b0ba3ce831b86407657af938817d7cd32f6cc796895bb',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 2, '03', '06', '6553f100', '03f5476a00', '027f4b', '2d1b28bb956a25f98133ca797a993a14fddbec80', '16345785d8a0000', '', []
            ],
            [
                '02f87701831e7d67850218711a008514419aa60082520894f4dd654afefe0f3d3b327e62a2a0b6430cd69a838804d95d7b3df8cc0080c001a0bab3626e10d4cc55f51bc9df67f2d59f81947f0d665dfa4eeca1dc4064d23f83a01d4d624b707271a20bf311b8d59bfa9479de89cd65bedae65b804b5ad7afd221',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 2, 1, '1e7d67', '0218711a00', '14419aa600', '5208', 'f4dd654afefe0f3d3b327e62a2a0b6430cd69a83', '04d95d7b3df8cc00', '', []
            ],
            [
                '02f8b30183018c238476fff487850a7578b0718287eb9495ad61b0a150d79219dcf64e1e6cc01f0b64c4ce80b844a9059cbb000000000000000000000000e78c04578e0ca1437a21dc53e5dfce1eb56783140000000000000000000000000000000000000000000083c3e2da94c316aa7800c001a0362268ea5bd51267ab828506260ef901fa1167949ee8003cb8933476dd16e033a07af426fa9de9a27066d7a5b27d919cc00ee8162a422f10d5770ed045f5397352',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 2, 1, '018c23', '76fff487', '0a7578b071', '87eb', '95ad61b0a150d79219dcf64e1e6cc01f0b64c4ce', '', 'a9059cbb000000000000000000000000e78c04578e0ca1437a21dc53e5dfce1eb56783140000000000000000000000000000000000000000000083c3e2da94c316aa7800', []
            ],
            [
                '02f87701831e7d67850218711a008514419aa60082520894f4dd654afefe0f3d3b327e62a2a0b6430cd69a838804d95d7b3df8cc0080c001a0bab3626e10d4cc55f51bc9df67f2d59f81947f0d665dfa4eeca1dc4064d23f83a01d4d624b707271a20bf311b8d59bfa9479de89cd65bedae65b804b5ad7afd221',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 2, 1, '1e7d67', '0218711a00', '14419aa600', '5208', 'f4dd654afefe0f3d3b327e62a2a0b6430cd69a83', '04d95d7b3df8cc00', '', []
            ],
        ];
    }

    private function hexify(string $val)
    {
        return strlen($val) % 2 === 1 ? '0' . $val : $val;
    }

    public function testBadPrivateKey () {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Incorrect private key');

        $transaction = new WrappedTransaction();
        $transaction->getRaw('');
    }
}
