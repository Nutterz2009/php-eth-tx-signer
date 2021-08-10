<?php

namespace nutterz2009\Ethereum;

use nutterz2009\RLP\RLP;
use kornrunner\Keccak;
use kornrunner\Secp256k1;

class LegacyTransaction
{
    protected $nonce;
    protected $gasPrice;
    protected $gasLimit;
    protected $to;
    protected $value;
    protected $data;
    protected $r = '';
    protected $s = '';
    protected $v = '';

    public function __construct(string $nonce = '', string $gasPrice = '', string $gasLimit = '', string $to = '', string $value = '', string $data = '') {
        $this->nonce = $nonce;
        $this->gasPrice = $gasPrice;
        $this->gasLimit = $gasLimit;
        $this->to = $to;
        $this->value = $value;
        $this->data = $data;
    }

    public function getInput(): array {
        return [
            'nonce' => $this->nonce,
            'gasPrice' => $this->gasPrice,
            'gasLimit' => $this->gasLimit,
            'to' => $this->to,
            'value' => $this->value,
            'data' => $this->data,
            'v' => $this->v,
            'r' => $this->r,
            's' => $this->s,
        ];
    }

    public function getRaw(string $privateKey, int $chainId = 0): string {
        $this->v = '';
        $this->r = '';
        $this->s = '';

        if (strlen($privateKey) != 64) {
            throw new \RuntimeException('Incorrect private key');
        }

        $this->sign($privateKey, $chainId);

        return $this->serialize();
    }

    protected function serialize(): string {
        return $this->RLPencode($this->getInput());
    }

    protected function sign(string $privateKey, int $chainId): void {
        $hash      = $this->hash($chainId);

        $secp256k1 = new Secp256k1();
        $signed    = $secp256k1->sign($hash, $privateKey);

        $this->r   = $this->hexup(gmp_strval($signed->getR(), 16));
        $this->s   = $this->hexup(gmp_strval($signed->getS(), 16));
        $this->v   = $this->hexup(dechex($signed->getRecoveryParam ($hash, $privateKey) + 27 + ($chainId ? $chainId * 2 + 8 : 0)));
    }

    protected function hash(int $chainId): string {
        $input = $this->getInput();

        if ($chainId > 0) {
            $input['v'] = $chainId;
            $input['r'] = '';
            $input['s'] = '';
        } else {
            unset($input['v']);
            unset($input['r']);
            unset($input['s']);
        }

        $encoded = $this->RLPencode($input);

        return Keccak::hash(hex2bin($encoded), 256);
    }

    protected function RLPencode(array $input): string {
        $rlp  = new RLP;

        $data = [];
        foreach ($input as $item) {
            $data[] = $item ? '0x' . $this->hexup($item) : '';
        }

        return $rlp->encode($data);
    }

    private function hexup(string $value): string {
        return strlen ($value) % 2 == 1 ? "0{$value}" : $value;
    }
}
