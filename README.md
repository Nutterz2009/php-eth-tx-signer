# php-eth-tx-signer 

PHP Ethereum Raw Transaction Signer

Rewritten from kornrunner/ethereum-offline-raw-tx

## Usage

```php
use nutter2009\Ethereum\LegacyTransaction;

$chainId  = 1;
$nonce    = '04';
$gasPrice = '03f5476a00';
$gasLimit = '027f4b';
$to       = '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72';
$value    = '2a45907d1bef7c00';

$privateKey = 'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898';

$transaction = new LegacyTransaction ($chainId, $nonce, $gasPrice, $gasLimit, $to, $value);
$transaction->getRaw ($privateKey);
// f86d048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c00801ba0e68be766b40702e6d9c419f53d5e053c937eda36f0e973074d174027439e2b5da0790df3e4d0294f92d69104454cd96005e21095efd5f2970c2829736ca39195d8
```

With wrapped transactions

```php
use nutterz2009\Ethereum\WrappedTransaction;

$type     = 2; 
$nonce    = '04';
$gasPrice = '03f5476a00';
$gasLimit = '027f4b';
$to       = '1a8c8adfbe1c59e8b58cc0d515f07b7225f51c72';
$value    = '2a45907d1bef7c00';
$chainId  = 3;

$privateKey = 'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898';

$transaction = new WrappedTransaction($type, $chainId, $nonce, $gasPrice, $gasLimit, $to, $value);
$transaction->getRaw ($privateKey);
// f86d048503f5476a0083027f4b941a8c8adfbe1c59e8b58cc0d515f07b7225f51c72882a45907d1bef7c008025a0db4efcc22a7d9b2cab180ce37f81959412594798cb9af7c419abb6323763cdd5a0631a0c47d27e5b6e3906a419de2d732e290b73ead4172d8598ce4799c13bda69
```

## Crypto
If you think this is useful, be a kind gentleman

ETH 0x23c10D37025Db9BF05f5690095eD0162804114D5
