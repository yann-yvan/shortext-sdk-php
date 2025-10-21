<?php

namespace NyCorp\Shortext\Sdk\Flows;

use Exception;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\RSA;

class FlowCrypto
{
    public function decryptRequest(array $body, string $privatePem): FlowCryptoData
    {
        $encryptedAesKey = base64_decode($body['encrypted_aes_key'], true);
        $encryptedFlowData = base64_decode($body['encrypted_flow_data']);
        $initialVector = base64_decode($body['initial_vector']);

        // Decrypt the AES key created by the client
        $rsa = RSA::load($privatePem)
            ->withPadding(RSA::ENCRYPTION_OAEP)
            ->withHash('sha256')
            ->withMGFHash('sha256');

        try {
            $decryptedAesKey = $rsa->decrypt($encryptedAesKey);
        } catch (\Throwable $e) {
            throw new Exception("<strong> RSA decrypt error: {$e->getMessage()} </strong>");
        }

        if ($decryptedAesKey === false) {
            throw new Exception('RSA decrypt returned false. Check OAEP hash and padding.');
        }

        // Decrypt the Flow data
        $aes = new AES('gcm');
        $aes->setKey($decryptedAesKey);
        $aes->setNonce($initialVector);
        $tagLength = 16;
        $encryptedFlowDataBody = substr($encryptedFlowData, 0, -$tagLength);
        $encryptedFlowDataTag = substr($encryptedFlowData, -$tagLength);
        $aes->setTag($encryptedFlowDataTag);

        $decrypted = $aes->decrypt($encryptedFlowDataBody);
        if (! $decrypted) {
            throw new Exception('Decryption of flow data failed.');
        }

        $this->cryptoData = new FlowCryptoData(json_decode($decrypted, true), $decryptedAesKey, $initialVector);

        return $this->cryptoData;
    }

    public function encrypt($response, FlowCryptoData $cryptoData = null): string
    {
        $cryptoData = $cryptoData ?? $this->cryptoData;

        // Flip the initialization vector
        $flipped_iv = ~$cryptoData->getInitialVectorBuffer();

        // Encrypt the response data
        $cipher = openssl_encrypt(json_encode($response), 'aes-128-gcm', $cryptoData->getAesKeyBuffer(), OPENSSL_RAW_DATA, $flipped_iv, $tag);
        return base64_encode($cipher . $tag);
    }
}
