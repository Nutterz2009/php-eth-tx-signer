# php-eth-tx-signer

PHP Ethereum Offline Raw Transaction Signer

Rewritten from `kornrunner/ethereum-offline-raw-tx`

Current Version: `V0.3.0`

--- 

# <em style="color:#FF0000">Warning:</em>
<div style="color:#FF0000">Version 0.3.0 may contain BREAKING changes:</div>
<div>Please check your code before updating!!!</div>

```
NOW:
   (int) '0'    => 0x80    <----
(string) '0'    => 0x80    <----
(string) ''     => 0x80    <----
(string) '0x80' => 0x8180  <---- 

Before:
   (int) '0'    => 0x80    <----
(string) '0'    => 0x80    <----
(string) ''     => 0x80    <----
(string) '0x80' => 0x80    <---- this was the real problem,
                           ^^^^^  txs were not properly encoded at this nonce
                           ^^^^^  could of caused other problems on tx encoding 
Any values > 0x80 are NOT changed.
```

---
## Usage

```php
use nutter2009\Ethereum\LegacyTransaction;

$chainId  = '1';
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

$type     = '2'; 
$nonce    = '06';
$maxPriorityFeePerGas = '6553f100';
$maxFeePerGas = '03f5476a00';
$gasLimit = '027f4b';
$to       = '2d1b28bb956a25f98133ca797a993a14fddbec80';
$value    = '16345785d8a0000';
$chainId  = '3';
$data     = '';
$accessList = [];

$privateKey = 'b2f2698dd7343fa5afc96626dee139cb92e58e5d04e855f4c712727bf198e898';

$transaction = new WrappedTransaction($type, $chainId, $nonce, $maxPriorityFeePerGas, $maxFeePerGas, $gasLimit, $to, $value, $data, $accessList);
$transaction->getRaw ($privateKey);
// 02f8740306846553f1008503f5476a0083027f4b942d1b28bb956a25f98133ca797a993a14fddbec8088016345785d8a000080c080a0d0c5504fc08c5cfa5ed78b86ca96f02df9959e8aeb7bf0c7d8e9844f61dbc469a042c4503ae4141a06905b0ba3ce831b86407657af938817d7cd32f6cc796895bb
```

## Crypto
If you think this is useful, be a kind gentleman

ETH 0x23c10D37025Db9BF05f5690095eD0162804114D5
