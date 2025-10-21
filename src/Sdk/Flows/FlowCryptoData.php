<?php

namespace NyCorp\Shortext\Sdk\Flows;

class FlowCryptoData
{
    private array $decryptedBody = [];

    private string $aesKeyBuffer;

    private string $initialVectorBuffer;

    public function __construct(array $decryptedBody, string $aesKeyBuffer, string $initialVectorBuffer)
    {
        $this->decryptedBody = $decryptedBody;
        $this->aesKeyBuffer = $aesKeyBuffer;
        $this->initialVectorBuffer = $initialVectorBuffer;
    }

    public function getDecryptedBody(): array
    {
        return $this->decryptedBody;
    }

    public function getAesKeyBuffer(): string
    {
        return $this->aesKeyBuffer;
    }

    public function getInitialVectorBuffer(): string
    {
        return $this->initialVectorBuffer;
    }
}
