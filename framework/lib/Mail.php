<?php

require_once('PHPMailer/class.phpmailer.php');

class Mail
{
	private $sender;
	private $recipient;
	private $subject;
	private $contentHtml;
	private $contentPlain;

	public function __construct() {
		$this->sender       = array('info@bookpile.net', 'bookpile.net');
		$this->subject	    = 'bookpile.net testmail';
	}

	public function setTo($recipient) {
		if(is_array($recipient)) {
			$this->recipient = $recipient;
		} else {
			$this->recipient = array($recipient, null);
		}
	}

	public function setFrom($sender) {
		$this->sender = $sender;
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function setHtml($htmlContent) {
		$this->contentHtml = $htmlContent;
	}

	public function setPlain($plainContent) {
		$this->contentPlain = $plainContent;
	}

	public function send() {
		switch(_MAILER) {
			case 'PHPMailer':
				$mail = new PHPMailer(true);
				if(_MAILER_USE_SMTP === true) {
					$mail->IsSMTP(); // telling the class to use SMTP
					/*$mail->SMTPAuth = true; // enable SMTP authentication
					$mail->SMTPSecure = "ssl"; // sets the prefix to the servier
					$mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
					$mail->Port = 465; // set the SMTP port for the GMAIL server
					$mail->Username = ""; // GMAIL username
					$mail->Password = ""; // GMAIL password*/
				}
				$mail->SetFrom($this->sender[0]);
				$mail->AddAddress($this->recipient[0], $this->recipient[1]);
				$mail->Subject = $this->subject;
				if(isset($this->contentHtml)) {
					$mail->isHTML(true);
					$mail->Body    = $this->contentHtml;
					if(isset($this->contentPlain)) {
						$mail->AltBody = $this->contentPlain;
					}
				} else {
					$mail->Body    = $this->contentPlain;
				}
				$result = $mail->Send();
				break;
			default:
				throw new Exception('There is no valid mailer class defined in config.php', 6);
				break;
		}

		return $result;
	}
}

?>