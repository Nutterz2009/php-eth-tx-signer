<?php

namespace nutterz2009\Ethereum;

use kornrunner\Keccak;
use kornrunner\Secp256k1;
use nutterz2009\RLP\RLP;
use nutterz2009\SharedTrait;
use RuntimeException;

class WrappedTransaction {
    use SharedTrait;

    protected $type;
    protected $chainId;
    protected $nonce;
    protected $maxPriorityFeePerGas;
    protected $maxFeePerGas;
    protected $gasLimit;
    protected $to;
    protected $value;
    protected $data;
    protected $accessList;
    protected $r = '';
    protected $s = '';
    protected $y = '';

    public function __construct(string $type = '',
                                string $chainId = '',
                                string $nonce = '',
                                string $maxPriorityFeePerGas = '',
                                string $maxFeePerGas = '',
                                string $gasLimit = '',
                                string $to = '',
                                string $value = '',
                                string $data = '',
                                array $accessList = []
    ) {
        $this->type = $type;
        $this->chainId = $chainId;
        $this->nonce = $nonce;
        $this->maxPriorityFeePerGas = $maxPriorityFeePerGas;
        $this->maxFeePerGas = $maxFeePerGas;
        $this->gasLimit = $gasLimit;
        $this->to = $to;
        $this->value = $value;
        $this->data = $data;
        $this->accessList = $accessList;
    }

    public function getInput(): array {
        return [
            'chainId' => $this->chainId,
            'nonce' => $this->nonce,
            'maxPriorityFeePerGas' => $this->maxPriorityFeePerGas,
            'maxFeePerGas' => $this->maxFeePerGas,
            'gasLimit' => $this->gasLimit,
            'to' => $this->to,
            'value' => $this->value,
            'data' => $this->data,
            'accessList' => $this->accessList,
            'y' => $this->y,
            'r' => $this->r,
            's' => $this->s,
        ];
    }

    public function getRaw(string $privateKey): string {
        if ($this->chainId < 0) {
            throw new RuntimeException('ChainID must be positive');
        }

        $this->y = '';
        $this->r = '';
        $this->s = '';

        if (strlen($privateKey) != 64) {
            throw new RuntimeException('Incorrect private key');
        }

        $this->sign($privateKey);

        return $this->padHex(dechex((int) $this->type)) . $this->serialize();
    }

    private function serialize(): string {
        return $this->RLPencode($this->getInput());
    }

    private function sign(string $privateKey): void {
        $hash = $this->hash();
        $secp256k1 = new Secp256k1();

        $signed = $secp256k1->sign($hash, $privateKey);

        $this->r = $this->padHex(gmp_strval($signed->getR(), 16));
        $this->s = $this->padHex(gmp_strval($signed->getS(), 16));
        if ($signed->getRecoveryParam() % 2 === 1) {
            $this->y = '01';
        } else {
            $this->y = '';
        }
    }

    protected function hash(): string {
        $input = $this->getInput();

        unset($input['y']);
        unset($input['r']);
        unset($input['s']);

        $encoded = $this->RLPencode($input);

        return Keccak::hash(hex2bin($this->padHex($this->type)) . hex2bin($encoded), 256);
    }

    private function RLPencode(array $input): string
    {
        $rlp = new RLP;

        return $rlp->encode($this->addRLPItem($input));
    }

    private function addRLPItem($input)
    {
        $output = [];

        foreach ($input as $item) {
            if (is_array($item)) {
                $output[] = $this->addRLPItem($item);
            } else {
                $output[] = $item ? '0x' . $this->padHex($this->stripHexPrefix($item)) : '';
            }
        }

        return $output;
    }
}
