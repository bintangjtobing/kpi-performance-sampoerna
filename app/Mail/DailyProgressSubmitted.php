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
    public $feedbackMessage;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, DailyProgress $dailyProgress, $overallPercentage, $feedbackMessage, $pdfPath = null)
    {
        $this->user = $user;
        $this->dailyProgress = $dailyProgress;
        $this->overallPercentage = $overallPercentage;
        $this->feedbackMessage = $feedbackMessage;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject('Laporan Harian KPI Berhasil Dikirim - ' . now()->format('d/m/Y'))
                     ->view('emails.daily-progress-submitted')
                     ->with([
                         'user' => $this->user,
                         'dailyProgress' => $this->dailyProgress,
                         'overallPercentage' => $this->overallPercentage,
                         'feedbackMessage' => $this->feedbackMessage,
                         'pdfPath' => $this->pdfPath,
                     ]);
                     
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $mail->attach($this->pdfPath, [
                'as' => 'Laporan_Harian_KPI_' . now()->format('d-m-Y') . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }
        
        return $mail;
    }
}
