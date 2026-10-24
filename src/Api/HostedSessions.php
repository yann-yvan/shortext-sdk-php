<?php

namespace NyCorp\Shortext\Api;

use NyCorp\Shortext\Page;
use NyCorp\Shortext\Resource;

/**
 * /v2.0/hosted-sessions: signed, expiring links to Shortext pages (connect a WhatsApp number, a mailbox...)
 * to give to your own users (permission manage_integrations).
 */
class HostedSessions extends ApiResource
{
    /**
     * Available purposes and their description, e.g. ['whatsapp' => 'Connect WhatsApp Business numbers...']
     *
     * @return array<string, string>
     */
    public function purposes(): array
    {
        $body = $this->request('GET', '/hosted-sessions/purposes')->body();

        return is_array($body) ? $body : [];
    }

    /**
     * The `url` of the response is the page to give to your user: it holds a secret, never log or share it.
     *
     * @param  array<string, mixed>  $context  options of the purpose, e.g. ['agent_id' => 'AGI-XXXXX']
     * @param  string|null  $returnUrl  your page the user is sent back to, with ?session_id=...&status=...
     * @param  int|null  $expiresIn  minutes (5 to 1440, default 60)
     */
    public function create(string $purpose, array $context = [], ?string $returnUrl = null, ?string $locale = null, ?int $expiresIn = null): Resource
    {
        return $this->item($this->request('POST', '/hosted-sessions', json: array_filter([
            'purpose' => $purpose,
            'context' => $context === [] ? null : $context,
            'return_url' => $returnUrl,
            'locale' => $locale,
            'expires_in' => $expiresIn,
        ], static fn ($value) => $value !== null)));
    }

    /** @return Page<resource> */
    public function list(int $page = 1): Page
    {
        return $this->page('/hosted-sessions', [], $page);
    }

    public function get(string $id): Resource
    {
        return $this->item($this->request('GET', '/hosted-sessions/'.self::id($id)));
    }

    public function cancel(string $id): Resource
    {
        return $this->item($this->request('DELETE', '/hosted-sessions/'.self::id($id)));
    }
}
