<?php

namespace App\Mail;

use App\Models\Invitation;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $acceptUrl;
    public string $tenantNombre;
    public string $rol;

    public function __construct(
        public readonly Invitation $invitation,
        public readonly Tenant $tenant,
    ) {
        $frontendUrl = rtrim(config('app.frontend_url', config('app.url')), '/');
        $this->acceptUrl  = $frontendUrl . '/#/invitacion/' . $invitation->token;
        $this->tenantNombre = $tenant->nombre ?? $tenant->id;
        $this->rol = $invitation->rol;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invitación para unirte a {$this->tenantNombre}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
        );
    }
}
