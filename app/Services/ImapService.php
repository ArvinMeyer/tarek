<?php

namespace App\Services;

use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;
use App\Models\Email;
use App\Models\EmailAttachment;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class ImapService
{
    protected ?Client $client = null;

    public function __construct()
    {
        $this->connect();
    }

    protected function connect()
    {
        try {
            $config = [
                'host' => Setting::get('imap_host'),
                'port' => Setting::get('imap_port', 993),
                'encryption' => 'ssl',
                'validate_cert' => true,
                'username' => Setting::get('imap_username'),
                'password' => Setting::get('imap_password'),
                'protocol' => 'imap',
            ];

            $cm = new ClientManager();
            $this->client = $cm->make($config);
            $this->client->connect();
        } catch (\Exception $e) {
            \Log::error('IMAP connection failed: ' . $e->getMessage());
            $this->client = null;
        }
    }

    public function fetchEmails(int $limit = 50)
    {
        if (!$this->client) {
            return;
        }

        try {
            $folder = $this->client->getFolder('INBOX');
            $messages = $folder->messages()->all()->limit($limit)->get();

            foreach ($messages as $message) {
                $this->storeEmail($message);
            }
        } catch (\Exception $e) {
            \Log::error('IMAP fetch failed: ' . $e->getMessage());
        }
    }

    protected function storeEmail($message)
    {
        try {
            $messageId = $message->getMessageId();

            // Skip if already stored
            if (Email::where('message_id', $messageId)->exists()) {
                return;
            }

            $from = $message->getFrom()[0] ?? null;
            $to = $message->getTo()[0] ?? null;

            $email = Email::create([
                'message_id' => $messageId,
                'from_email' => $from ? $from->mail : '',
                'from_name' => $from ? $from->personal : '',
                'to_email' => $to ? $to->mail : '',
                'subject' => $message->getSubject(),
                'body_html' => $message->getHTMLBody(),
                'body_text' => $message->getTextBody(),
                'is_read' => $message->hasFlag('seen'),
                'is_sent' => false,
                'has_attachments' => $message->hasAttachments(),
                'email_date' => $message->getDate(),
            ]);

            // Auto-link to customer
            Email::autoLinkToCustomer($email);

            // Store attachments
            if ($message->hasAttachments()) {
                foreach ($message->getAttachments() as $attachment) {
                    $this->storeAttachment($email, $attachment);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error storing email: ' . $e->getMessage());
        }
    }

    protected function storeAttachment($email, $attachment)
    {
        try {
            $filename = $attachment->getName();
            $path = 'email_attachments/' . $email->id . '/' . $filename;
            
            Storage::put($path, $attachment->getContent());

            EmailAttachment::create([
                'email_id' => $email->id,
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $attachment->getContentType(),
                'file_size' => $attachment->getSize(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error storing attachment: ' . $e->getMessage());
        }
    }

    public function markAsRead(Email $email)
    {
        if (!$this->client || !$email->message_id) {
            return false;
        }

        try {
            $folder = $this->client->getFolder('INBOX');
            $message = $folder->query()->messageId($email->message_id)->get()->first();
            
            if ($message) {
                $message->setFlag('Seen');
                $email->markAsRead();
                return true;
            }
        } catch (\Exception $e) {
            \Log::error('Error marking as read: ' . $e->getMessage());
        }

        return false;
    }

    public function markAsUnread(Email $email)
    {
        if (!$this->client || !$email->message_id) {
            return false;
        }

        try {
            $folder = $this->client->getFolder('INBOX');
            $message = $folder->query()->messageId($email->message_id)->get()->first();
            
            if ($message) {
                $message->unsetFlag('Seen');
                $email->markAsUnread();
                return true;
            }
        } catch (\Exception $e) {
            \Log::error('Error marking as unread: ' . $e->getMessage());
        }

        return false;
    }
}

