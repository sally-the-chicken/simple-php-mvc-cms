<?php

class Util_Email
{
    public static function send(array $options)
    {
        $default_options = array(
            'to' => null,
            'subject' => null,
            'message' => null,
            'from' => null,
            'cc' => null,
            'mail_options' => null,
        );

        $options = array_merge($default_options, $options);

        foreach (array('to', 'from', 'subject') as $required_field) {
            if (empty($options[$required_field])) {
                throw new Exception("$required_field is missing.");
            }
        }

        extract($options);
        $additional_headers = "From: $from \r\n" . "Reply-To: $from \r\n";
        $additional_headers .= "MIME-Version: 1.0\r\n";
        $additional_headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        if (!empty($cc)) {
            $additional_headers .= 'Cc: ' . $cc . "\r\n";
        }
        mail($to, $subject, $message, $additional_headers, $mail_options);

    }
}
