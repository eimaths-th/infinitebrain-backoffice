<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $url1;
    public $url2;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
        $this->url1 = '<img height="200px" src="' . asset('receipt_form_images/' . $data->photo_att) . '" alt="..." style="vertical-align: middle;">';
        $this->url2 = '<img src=" ' . asset('receipt_form_images/' . $data->file_att) . ' " alt="..." style="vertical-align: middle; height: 200px;">';
        // $this->url2 = asset('receipt_form_images/' . $data->file_att);
        if ($data->sfield == '17') {
            $this->subject('Math League');
        }else {
            $this->subject('Contact Mail');

        }
        // dd($data,$this->url1,$this->url2);

        // $this->url1 = asset('receipt_form_images/' . $data->photo_att);
        // $this->url2 = asset('receipt_form_images/' . $data->file_att);
        // $this->url2 = asset('receipt_form_images/' . $data->file_att);

        // dd($data,$this->url1,$this->url2);

    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    // public function envelope()
    // {
    //     return new Envelope(
    //         subject: 'Contact Mail',
    //     );
    // }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.contact',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
