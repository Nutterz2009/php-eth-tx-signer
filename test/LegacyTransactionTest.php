<?php

namespace nutterz2009;

use nutterz2009\Ethereum\LegacyTransaction;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Web3p\RLP\RLP;

class LegacyTransactionTest extends TestCase {

    /**
     * @dataProvider input
     * @param $expect
     * @param $nonce
     * @param $gasPrice
     * @param $gasLimit
     * @param $to
     * @param $value
     * @param $data
     */
    public function testGetInput ($expect, $nonce, $gasPrice, $gasLimit, $to, $value, $data) {
        $transaction = new LegacyTransaction ($nonce, $gasPrice, $gasLimit, $to, $value, $data);
        $this->assertSame($expect, $transaction->getInput());
    }

    public static function input (): array {
        return [
            [
                ['nonce' => '', 'gasPrice' => '', 'gasLimit' => '', 'to' => '', 'value' => '', 'data' => '', 'v' => '', 'r' => '', 's' => ''],
                '', '', '', '', '', ''
            ],
            [
                ['nonce' => '04', 'gasPrice' => '03f5476a00', 'gasLimit' => '027f4b', 'to' => '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', 'value' => '2a45907d1bef7c00', 'data' => '', 'v' => '', 'r' => '', 's' => ''],
                '04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', ''
            ],
        ];
    }

    /**
     * @dataProvider getRaw
     * @param $expect
     * @param $privateKey
     * @param $chainId
     * @param $nonce
     * @param $gasPrice
     * @param $gasLimit
     * @param $to
     * @param $value
     * @param $data
     */
    public function testGetRaw ($expect, $privateKey, $chainId, $nonce, $gasPrice, $gasLimit, $to, $value, $data) {
        $transaction = new LegacyTransaction ($nonce, $gasPrice, $gasLimit, $to, $value, $data);
        $this->assertSame($expect, $transaction->getRaw($privateKey, $chainId));
    }

    /**
     * @dataProvider getRaw
     * @param $expect
     * @param $privateKey
     * @param $chainId
     * @param $nonce
     * @param $gasPrice
     * @param $gasLimit
     * @param $to
     * @param $value
     * @param $data
     */
    public function testDecodeRaw ($expect, $privateKey, $chainId, $nonce, $gasPrice, $gasLimit, $to, $value, $data) {

        $rlp = new RLP;
        $decoded = $rlp->decode($expect);

        $this->assertSame($decoded[0], $this->hexify($nonce));
        $this->assertSame($decoded[1], $this->hexify($gasPrice));
        $this->assertSame($decoded[2], $this->hexify($gasLimit));
        $this->assertSame($decoded[3], $this->hexify($to));
        $this->assertSame($decoded[4], $this->hexify($value));
        $this->assertSame($decoded[5], $this->hexify($data));
    }

    private function hexify(string $val)
    {
        return strlen($val) % 2 === 1 ? '0' . $val : $val;
    }

    public static function getRaw (): array {
        return [
            [
                'f86d048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c00801ba0e68be766b40702e6d9c419f53d5e053c937eda36f0e973074d174027439e2b5da0790df3e4d0294f92d69104454cd96005e21095efd5f2970c2829736ca39195d8',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 0, '04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', ''
            ],
            [
                'f86c048503f5476a0083027f4b942d1b28bb956a25f98133ca797a993a14fddbec8088043a280a6a5a0c008029a0dec4ff083432bc261350349e3305dcbb64a09cfffff9bf41a1aa726363a35f239f98e70ba73cbc906dfd9a7a934528fe01e990a453fbaec56e0062693854b3dc',
                '4669f91636c1d4a23c3f467a8aff2ca12cfb5ab74c29bd1b9175a17df4d491eb', 3, '04', '03f5476a00', '027f4b', '2d1b28bb956a25f98133ca797a993a14fddbec80', '43a280a6a5a0c00', ''
            ],
            [
                'f86d058503f5476a0083027f4b942d1b28bb956a25f98133ca797a993a14fddbec8088043a280a6a5a0c00802aa0ece7d14850ec66506f31ce20cd64225c2e4dedff292389a1bc02bf1269000753a01e19aedf96cbec98c4abd6304806c006d42bac10dbb5b9fec99504f128a3bd57',
                '4669f91636c1d4a23c3f467a8aff2ca12cfb5ab74c29bd1b9175a17df4d491eb', 3, '05', '03f5476a00', '027f4b', '2d1b28bb956a25f98133ca797a993a14fddbec80', '43a280a6a5a0c00', ''
            ],
            [
                'f86d048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c008025a0db4efcc22a7d9b2cab180ce37f81959412594798cb9af7c419abb6323763cdd5a0631a0c47d27e5b6e3906a419de2d732e290b73ead4172d8598ce4799c13bda69',
                'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898', 1, '04', '03f5476a00', '027f4b', '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72', '2a45907d1bef7c00', ''
            ],
        ];
    }

    public function testBadPrivateKey () {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Incorrect private key');

        $transaction = new LegacyTransaction();
        $transaction->getRaw('');
    }
}
