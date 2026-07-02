<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\ReporteGenerado;

class ReporteGeneradoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reporte;
    public $rutaArchivo;

    /**
     * Create a new message instance.
     */
    public function __construct(ReporteGenerado $reporte, $rutaArchivo)
    {
        $this->reporte = $reporte;
        $this->rutaArchivo = $rutaArchivo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📊 Reporte Generado - Consultorio Medico Yeshua',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reporte-generado',
            with: [
                'reporte' => $this->reporte,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->rutaArchivo)
                ->as(basename($this->rutaArchivo))
                ->withMime($this->reporte->formato === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}

