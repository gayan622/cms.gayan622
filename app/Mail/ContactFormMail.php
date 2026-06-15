<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Gayan622] New message: ' . ($this->contactMessage->subject ?: 'Contact form submission'),
            replyTo: [$this->contactMessage->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
        );
    }
}
