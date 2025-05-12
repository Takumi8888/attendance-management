<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMailable extends Mailable
{
    use Queueable, SerializesModels;

	public $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function build()
	{
		return $this
			->subject('サンプルメールの件名')
			->with(['data' => $this->data,])
			->redirect('http://localhost:8025/');
	}
}
