<?php

namespace Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $config = [];

    /**
     * config default for send mail
     * ex: from_name, from, to, reply_to, cc, bcc, subject, view, data
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function build()
    {
        $fromName = !empty($this->config['from_name']) ? $this->config['from_name'] : env('MAIL_FROM_NAME');
        $fromName = $this->_encodeMimeHeader(mb_convert_encoding($fromName, "UTF-8", "UTF-8"));
        $data = !empty($this->config['data']) ? $this->config['data'] : [];

        // from
        $email = $this->from($this->config['from'], $fromName);

        // to
        $email->to($this->config['to']);

        // reply_to
        if (!empty($this->config['reply_to'])) {
            $email->replyTo($this->config['reply_to']);
        }

        // cc
        if (!empty($this->config['cc'])) {
            $email->cc($this->config['cc']);
        }

        // bcc
        if (!empty($this->config['bcc'])) {
            $email->bcc($this->config['bcc']);
        }

        // subject
        $email->subject($this->config['subject']);

        // view template and data
        $email->view($this->config['view'], $data);

        // attach file
        if (!empty($this->config['attachments'])) {
            if (is_array($this->config['attachments'])) {
                foreach ($this->config['attachments'] as $k => $attachment) {
                    $email->attach($k, $attachment);
                }
                return $email;
            }
            $email->attach($this->config['attachments']);
        }

        return $email;
    }

    /**
     * @param $string
     * @return false|string
     */
    protected function _encodeMimeHeader($string)
    {
        if (!strlen($string)) {
            return "";
        }

        $linefeed = "\r\n";

        $charset = 'utf-8';

        $start = "=?$charset?B?";
        $end = "?=";
        $encoded = '';

        /* Each line must have length <= 75, including $start and $end */
        $length = 75 - strlen($start) - strlen($end);
        /* Average multi-byte ratio */
        $ratio = mb_strlen($string, $charset) / strlen($string);
        /* Base64 has a 4:3 ratio */
        $magic = $avgLength = floor(3 * $length * $ratio / 4);

        for ($i = 0; $i <= mb_strlen($string, $charset); $i += $magic) {
            $magic = $avgLength;
            $offset = 0;
            /* Recalculate magic for each line to be 100% sure */
            do {
                $magic -= $offset;
                $chunk = mb_substr($string, $i, $magic, $charset);
                $chunk = base64_encode($chunk);
                $offset++;
            } while (strlen($chunk) > $length);

            if ($chunk)
                $encoded .= ' ' . $start . $chunk . $end . $linefeed;
        }
        /* Chomp the first space and the last linefeed */
        return substr($encoded, 1, -strlen($linefeed));
    }
}
