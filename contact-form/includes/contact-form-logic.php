<?php
    /* This is where the contact form logic will be. */
    /* Interface to Captcha handler */
    class FG_CaptchaHandler {
        function Validate() { return false; }
        function GetError() { return ''; }
    }

    /* This is a general contact form, with support for Captcha, HTML
    Emails and file attachments. */
    class FGContactForm {
        var $recipients;
        var $errors;
        var $error_message;
        var $name;
        var $email;
        var $message;
        var $from_address;
        var $form_random_key;
        var $conditional_field;
        var $arr_conditional_recipients;
        var $fileupload_field;
        var $captcha_handler;

        var $mailer;

        function FGContactForm() {
            $this->recipients = array();
            $this->errors = array();
            /* Gotta find a better way to store this secret */
            $this->form_random_key = 'qshzDNmiEYIHuf4d0jXG';
            $this->conditional_field = '';
            $this->arr_conditional_recipients = array();
            $this->fileupload_fields = array();

            $this->mailer = new PHPMailer();
            $this->mailer->CharSet = 'utf-8';
'
        }
    }
?>
