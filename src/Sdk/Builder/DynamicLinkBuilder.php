<?php

namespace Nycorp\Shortext\Sdk\Builder;

class DynamicLinkBuilder implements PayloadBuilder
{

    protected string $fallback_url;
    protected string $deep_link;
    protected ?string $name = null;

    protected ?int $ttl = null;
    private ?string $android_package_name;
    private ?string $ios_package_name;

    public function setFallbackUrl(string $fallback_url): DynamicLinkBuilder
    {
        $this->fallback_url = $fallback_url;
        return $this;
    }

    public function setDeepLink(string $deep_link): DynamicLinkBuilder
    {
        $this->deep_link = $deep_link;
        return $this;
    }

    public function setAndroidPackageName(?string $android_package_name): DynamicLinkBuilder
    {
        $this->android_package_name = $android_package_name;
        return $this;
    }

    public function setIosPackageName(?string $ios_package_name): DynamicLinkBuilder
    {
        $this->ios_package_name = $ios_package_name;
        return $this;
    }


    public function setTtl(int $ttl): DynamicLinkBuilder
    {
        $this->ttl = $ttl;
        return $this;
    }

    public function setName(?string $name): DynamicLinkBuilder
    {
        $this->name = $name;
        return $this;
    }


    public function getPayload(): array
    {
        return [
            'fallback_url' => $this->fallback_url,
            'name' => $this->name,
            'deep_link' => $this->deep_link,
            'android_package_name' => $this->android_package_name,
            'ios_package_name' => $this->ios_package_name,
            'ttl' => $this->ttl,
        ];
    }

}
