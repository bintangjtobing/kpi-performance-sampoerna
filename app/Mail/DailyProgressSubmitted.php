<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\DailyProgress;

class DailyProgressSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $dailyProgress;
    public $overallPercentage;
    public $message;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, DailyProgress $dailyProgress, $overallPercentage, $message, $pdfPath = null)
    {
        $this->user = $user;
        $this->dailyProgress = $dailyProgress;
        $this->overallPercentage = $overallPercentage;
        $this->message = $message;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Harian KPI Berhasil Dikirim - ' . now()->format('d/m/Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-progress-submitted',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $attachments[] = Attachment::fromPath($this->pdfPath)
                ->as('Laporan_Harian_KPI_' . now()->format('d-m-Y') . '.pdf')
                ->withMime('application/pdf');
        }
        
        return $attachments;
    }
}
