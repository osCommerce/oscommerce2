<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

class Mail
{
    protected $to = [],
              $from = [],
              $cc = [],
              $bcc = [],
              $subject,
              $body_plain,
              $body_html,
              $attachments = [],
              $images = [],
              $headers = ['X-Mailer' => 'osCommerce'],
              $body,
              $content_transfer_encoding = '7bit',
              $charset = 'utf-8';

    public function __construct($to_email_address = null, $to = null, $from_email_address = null, $from = null, $subject = null)
    {
        if (!empty($to_email_address)) {
            $this->addTo($to_email_address, $to);
        }

        if (!empty($from_email_address)) {
            $this->setFrom($from_email_address, $from);
        }

        if (!empty($subject)) {
            $this->setSubject($subject);
        }
    }

    public function addTo($email_address, $name = null)
    {
        $this->to[] = [
            'name' => $name,
            'email_address' => $email_address
        ];
    }

    public function setFrom($email_address, $name = null)
    {
        $this->from = [
            'name' => $name,
            'email_address' => $email_address
        ];
    }

    public function addCC($email_address, $name = null)
    {
        $this->cc[] = [
            'name' => $name,
            'email_address' => $email_address
        ];
    }

    public function addBCC($email_address, $name = null)
    {
        $this->bcc[] = [
            'name' => $name,
            'email_address' => $email_address
        ];
    }

    public function clearTo()
    {
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->headers = [
            'X-Mailer' => 'osCommerce'
        ];
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setBody($html)
    {
        $plain = strip_tags($html);

        $this->setBodyHTML($html);
        $this->setBodyPlain($plain);
    }

    public function setBodyPlain($body)
    {
        $this->body_plain = $body;
        $this->body = null;
    }

    public function setBodyHTML($body)
    {
        $this->body_html = $body;
        $this->body = null;
    }

    public function setContentTransferEncoding($encoding)
    {
        $this->content_transfer_encoding = $encoding;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    public function addHeader($key, $value)
    {
        if ((strpos($key, "\n") !== false) || (strpos($key, "\r") !== false)) {
            return false;
        }

        if ((strpos($value, "\n") !== false) || (strpos($value, "\r") !== false)) {
            return false;
        }

        $this->headers[$key] = $value;
    }

    public function addAttachment($file, $is_uploaded = false)
    {
        if ($is_uploaded === true) {
        } elseif (file_exists($file) && is_readable($file)) {
            $data = file_get_contents($file);
            $filename = basename($file);
            $mimetype = $this->get_mime_type($filename);
        } else {
            return false;
        }

        $this->attachments[] = [
            'filename' => $filename,
            'mimetype' => $mimetype,
            'data' => chunk_split(base64_encode($data))
        ];
    }

    public function addImage($file, $is_uploaded = false)
    {
        if ($is_uploaded === true) {
        } elseif (file_exists($file) && is_readable($file)) {
            $data = file_get_contents($file);
            $filename = basename($file);
            $mimetype = $this->get_mime_type($filename);
        } else {
            return false;
        }

        $this->images[] = [
            'id' => md5(uniqid(time())),
            'filename' => $filename,
            'mimetype' => $mimetype,
            'data' => chunk_split(base64_encode($data))
        ];
    }

    public function send()
    {
        if (empty($this->body)) {
            if (!empty($this->body_plain) && !empty($this->body_html)) {
                $boundary = '=_____MULTIPART_MIXED_BOUNDARY____';
                $related_boundary = '=_____MULTIPART_RELATED_BOUNDARY____';
                $alternative_boundary = '=_____MULTIPART_ALTERNATIVE_BOUNDARY____';

                $this->headers['MIME-Version'] = '1.0';
                $this->headers['Content-Type'] = 'multipart/mixed; boundary="' . $boundary . '"';
                $this->headers['Content-Transfer-Encoding'] = $this->content_transfer_encoding;

                if (!empty($this->images)) {
                    foreach ($this->images as $image) {
                        $this->body_html = str_replace('src="' . $image['filename'] . '"', 'src="cid:' . $image['id'] . '"', $this->body_html);
                    }

                    unset($image);
                }

                $this->body = 'This is a multi-part message in MIME format.' . "\n\n" .
                              '--' . $boundary . "\n" .
                              'Content-Type: multipart/alternative; boundary="' . $alternative_boundary . '";' . "\n\n" .
                              '--' . $alternative_boundary . "\n" .
                              'Content-Type: text/plain; charset="' . $this->charset . '"' . "\n" .
                              'Content-Transfer-Encoding: ' . $this->content_transfer_encoding . "\n\n" .
                              $this->body_plain . "\n\n" .
                              '--' . $alternative_boundary . "\n" .
                              'Content-Type: multipart/related; boundary="' . $related_boundary . '"' . "\n\n" .
                              '--' . $related_boundary . "\n" .
                              'Content-Type: text/html; charset="' . $this->charset . '"' . "\n" .
                              'Content-Transfer-Encoding: ' . $this->content_transfer_encoding . "\n\n" .
                              $this->body_html . "\n\n";

                if (!empty($this->images)) {
                    foreach ($this->images as $image) {
                        $this->body .= $this->build_image($image, $related_boundary);
                    }

                    unset($image);
                }

                $this->body .= '--' . $related_boundary . '--' . "\n\n" .
                               '--' . $alternative_boundary . '--' . "\n\n";

                if (!empty($this->attachments)) {
                    foreach ($this->attachments as $attachment) {
                        $this->body .= $this->build_attachment($attachment, $boundary);
                    }

                    unset($attachment);
                }

                $this->body .= '--' . $boundary . '--' . "\n\n";
            } elseif (!empty($this->body_html) && !empty($this->images)) {
                $boundary = '=_____MULTIPART_MIXED_BOUNDARY____';
                $related_boundary = '=_____MULTIPART_RELATED_BOUNDARY____';

                $this->headers['MIME-Version'] = '1.0';
                $this->headers['Content-Type'] = 'multipart/mixed; boundary="' . $boundary . '"';

                foreach ($this->images as $image) {
                    $this->body_html = str_replace('src="' . $image['filename'] . '"', 'src="cid:' . $image['id'] . '"', $this->body_html);
                }

                unset($image);

                $this->body = 'This is a multi-part message in MIME format.' . "\n\n" .
                              '--' . $boundary . "\n" .
                              'Content-Type: multipart/related; boundary="' . $related_boundary . '";' . "\n\n" .
                              '--' . $related_boundary . "\n" .
                              'Content-Type: text/html; charset="' . $this->charset . '"' . "\n" .
                              'Content-Transfer-Encoding: ' . $this->content_transfer_encoding . "\n\n" .
                              $this->body_html . "\n\n";

                foreach ($this->images as $image) {
                    $this->body .= $this->build_image($image, $related_boundary);
                }

                unset($image);

                $this->body .= '--' . $related_boundary . '--' . "\n\n";

                foreach ($this->attachments as $attachment) {
                    $this->body .= $this->build_attachment($attachment, $boundary);
                }

                unset($attachment);

                $this->body .= '--' . $boundary . '--' . "\n";
            } elseif (!empty($this->attachments)) {
                $boundary = '=_____MULTIPART_MIXED_BOUNDARY____';
                $related_boundary = '=_____MULTIPART_RELATED_BOUNDARY____';

                $this->headers['MIME-Version'] = '1.0';
                $this->headers['Content-Type'] = 'multipart/mixed; boundary="' . $boundary . '"';

                $this->body = 'This is a multi-part message in MIME format.' . "\n\n" .
                              '--' . $boundary . "\n" .
                              'Content-Type: multipart/related; boundary="' . $related_boundary . '";' . "\n\n" .
                              '--' . $related_boundary . "\n" .
                              'Content-Type: text/' . (empty($this->body_plain) ? 'html' : 'plain') . '; charset="' . $this->charset . '"' . "\n" .
                              'Content-Transfer-Encoding: ' . $this->content_transfer_encoding . "\n\n" .
                              (empty($this->body_plain) ? $this->body_html : $this->body_plain) . "\n\n" .
                              '--' . $related_boundary . '--' . "\n\n";

                foreach ($this->attachments as $attachment) {
                    $this->body .= $this->build_attachment($attachment, $boundary);
                }

                unset($attachment);

                $this->body .= '--' . $boundary . '--' . "\n";
            } elseif (!empty($this->body_html)) {
                $this->headers['MIME-Version'] = '1.0';
                $this->headers['Content-Type'] = 'text/html; charset="' . $this->charset . '"';
                $this->headers['Content-Transfer-Encoding'] = $this->content_transfer_encoding;

                $this->body = $this->body_html . "\n";
            } else {
                $this->body = $this->body_plain . "\n";
            }
        }

        $to_email_addresses = [];

        foreach ($this->to as $to) {
            if ((strpos($to['email_address'], "\n") !== false) || (strpos($to['email_address'], "\r") !== false)) {
                return false;
            }

            if ((strpos($to['name'], "\n") !== false) || (strpos($to['name'], "\r") !== false)) {
                return false;
            }

            if (empty($to['name'])) {
                $to_email_addresses[] = $to['email_address'];
            } else {
                $to_email_addresses[] = '"' . $to['name'] . '" <' . $to['email_address'] . '>';
            }
        }

        unset($to);

        $cc_email_addresses = [];

        foreach ($this->cc as $cc) {
            if (empty($cc['name'])) {
                $cc_email_addresses[] = $cc['email_address'];
            } else {
                $cc_email_addresses[] = '"' . $cc['name'] . '" <' . $cc['email_address'] . '>';
            }
        }

        unset($cc);

        $bcc_email_addresses = [];

        foreach ($this->bcc as $bcc) {
            if (empty($bcc['name'])) {
                $bcc_email_addresses[] = $bcc['email_address'];
            } else {
                $bcc_email_addresses[] = '"' . $bcc['name'] . '" <' . $bcc['email_address'] . '>';
            }
        }

        unset($bcc);

        if (empty($this->from['name'])) {
            $this->addHeader('From', $this->from['email_address']);
        } else {
            $this->addHeader('From', '"' . $this->from['name'] . '" <' . $this->from['email_address'] . '>');
        }

        if (!empty($cc_email_addresses)) {
            $this->addHeader('Cc', implode(', ', $cc_email_addresses));
        }

        if (!empty($bcc_email_addresses)) {
            $this->addHeader('Bcc', implode(', ', $bcc_email_addresses));
        }

        $headers = '';

        foreach ($this->headers as $key => $value) {
            $headers .= $key . ': ' . $value . "\n";
        }

        if (empty($this->from['email_address']) || empty($to_email_addresses)) {
            return false;
        }

        if (empty($this->from['name'])) {
            ini_set('sendmail_from', $this->from['email_address']);
        } else {
            ini_set('sendmail_from', '"' . $this->from['name'] . '" <' . $this->from['email_address'] . '>');
        }

        mail(implode(', ', $to_email_addresses), $this->subject, $this->body, $headers, '-f' . $this->from['email_address']);

        ini_restore('sendmail_from');
    }

    protected function get_mime_type($file)
    {
        $ext = substr($file, strrpos($file, '.') + 1);

        $mime_types = [
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'bmp' => 'image/bmp',
            'png' => 'image/png',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'swf' => 'application/x-shockwave-flash'
        ];

        if (isset($mime_types[$ext])) {
            return $mime_types[$ext];
        } else {
            return 'application/octet-stream';
        }
    }

    protected function build_attachment($attachment, $boundary)
    {
        return '--' . $boundary . "\n" .
               'Content-Type: ' . $attachment['mimetype'] . '; name="' . $attachment['filename'] . '"' . "\n" .
               'Content-Disposition: attachment' . "\n" .
               'Content-Transfer-Encoding: base64' . "\n\n" .
                $attachment['data'] . "\n\n";
    }

    protected function build_image($image, $boundary)
    {
        return '--' . $boundary . "\n" .
               'Content-Type: ' . $image['mimetype'] . '; name="' . $image['filename'] . '"' . "\n" .
               'Content-ID: ' . $image['id'] . "\n" .
               'Content-Disposition: inline' . "\n" .
               'Content-Transfer-Encoding: base64' . "\n\n" .
                $image['data'] . "\n\n";
    }
}
