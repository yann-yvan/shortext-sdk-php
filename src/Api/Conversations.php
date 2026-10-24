<?php

namespace NyCorp\Shortext\Api;

use NyCorp\Shortext\Messages\Contact;
use NyCorp\Shortext\Messages\Message;
use NyCorp\Shortext\Page;
use NyCorp\Shortext\Resource;

/**
 * /v2.0/conversations: reading needs view_messages, acting needs send_messages.
 */
class Conversations extends ApiResource
{
    /**
     * @param array{
     *     status?: 'active'|'archived',
     *     agent_id?: string,
     *     channel?: 'whatsapp'|'mail'|'shortext',
     *     channel_id?: string,
     *     contact?: string,
     *     assignee?: string,
     *     ticket_id?: string,
     *     updated_since?: string|\DateTimeInterface,
     * } $filters assignee: "none" or a member id (assignees())
     * @return Page<resource>
     */
    public function list(array $filters = [], int $page = 1): Page
    {
        if (($filters['updated_since'] ?? null) instanceof \DateTimeInterface) {
            $filters['updated_since'] = $filters['updated_since']->format(\DateTimeInterface::ATOM);
        }

        return $this->page('/conversations', $filters, $page);
    }

    public function get(string $id): Resource
    {
        return $this->item($this->request('GET', '/conversations/'.self::id($id)));
    }

    /**
     * Messages of the conversation, most recent first (same data as the message.* webhooks)
     *
     * @return Page<resource>
     */
    public function messages(string $id, int $page = 1): Page
    {
        return $this->page('/conversations/'.self::id($id).'/messages', [], $page);
    }

    /**
     * Sends a first message to a contact from one of your channels. On WhatsApp, outside the 24h window
     * opened by the contact, only an approved template can be sent.
     *
     * @param  string  $channel  whatsapp, mail or shortext
     * @param  string  $channelId  the channel id (channels()->list())
     * @param  bool  $handover  true: a human follows the conversation, the AI agent does not answer
     * @return resource { conversation, message }
     */
    public function start(string $channel, string $channelId, Contact|string|array $contact, Message|array $message, bool $handover = false): Resource
    {
        return $this->item($this->request('POST', '/conversations', json: [
            'channel' => $channel,
            'channel_id' => $channelId,
            'contact' => Contact::from($contact)->toArray(),
            'message' => self::message($message),
            'handover' => $handover,
        ]));
    }

    /**
     * Replies to the contact. By default the reply takes the conversation over (the AI agent stops answering):
     * send $handover = false for a notification that lets the agent continue.
     *
     * @return resource the sent message
     */
    public function reply(string $id, Message|array $message, bool $handover = true): Resource
    {
        return $this->item($this->request('POST', '/conversations/'.self::id($id).'/messages', json: [
            'message' => self::message($message),
            'handover' => $handover,
        ]));
    }

    /** A human takes the conversation over: the AI agent stops answering (conversation.handover) */
    public function handover(string $id): Resource
    {
        return $this->item($this->request('PUT', '/conversations/'.self::id($id).'/handover'));
    }

    /** The AI agent answers again (conversation.released) */
    public function release(string $id): Resource
    {
        return $this->item($this->request('DELETE', '/conversations/'.self::id($id).'/handover'));
    }

    /** Assigns the current session to a member (assignees()), or unassigns it with null */
    public function assign(string $id, ?string $userId): Resource
    {
        return $this->item($this->request('PUT', '/conversations/'.self::id($id).'/assignee', json: ['user_id' => $userId]));
    }

    public function unassign(string $id): Resource
    {
        return $this->assign($id, null);
    }

    /**
     * Members of the company a conversation can be assigned to
     *
     * @return list<resource>
     */
    public function assignees(): array
    {
        return $this->items($this->request('GET', '/conversations/assignees'));
    }

    /** @return array<string, mixed> */
    private static function message(Message|array $message): array
    {
        return $message instanceof Message ? $message->toArray() : $message;
    }
}
