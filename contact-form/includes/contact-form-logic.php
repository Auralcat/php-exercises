<?php
    /* This is where the contact form logic will be. */

    /* Require PHPMailer */
    require_once("class.phpmailer.php");

    /* Interface to Captcha handler */
    class CaptchaHandler {
        function Validate() { return false; }
        function GetError() { return ''; }
    }

    /* ContactForm is a general purpose contact form class.
       It supports Captcha, HTML Emails, sending emails conditionally,
       file attachments and more.*/
    class ContactForm {
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

        function ContactForm() {
            $this->recipients = array();
            $this->errors = array();
            /* Gotta find a better way to store this secret */
            $this->form_random_key = 'qshzDNmiEYIHuf4d0jXG';
            $this->conditional_field = '';
            $this->arr_conditional_recipients = array();
            $this->fileupload_fields = array();

            $this->mailer = new PHPMailer();
            $this->mailer->CharSet = 'utf-8';
        }

        function EnableCaptcha($captcha_handler) {
            $this->captcha_handler = $captcha_handler;
            session_start();
        }

        function AddRecipient($email, $name="") {
            $this->mailer->AddAddress($email, $name);
        }

        function SetFromAddress($from) {
            $this->from_address = $from;
        }

        function SetFormRandomKey($key) {
            $this->form_random_key = $key;
        }

        function GetSpamTrapInputName() {
            return 'sp'.md5('yXZt4iu85L'.$this->GetKey());
        }

        function SafeDisplay($value_name) {
            if(empty_($_POST[$value_name])) {
                return '';
            }

            /* Functions in PHP are case-insensitive */
            return htmlEntities($_POST[$value_name]);
        }

        function GetFormIDInputName() {
            $rand = md5('r6NHYn2GRd').$this->GetKey();

            $rand = substr($rand, 0, 20);
            return $id.$rand;
        }

        function GetFormIDInputValue() {
            return md5('9uTijJrvwS'.$this->GetKey());
        }

        function SetConditionalField($field) {
            $this->conditional_field = $field;
        }

        function AddConditionalRecipient($value, $email) {
            $this->arr_conditional_recipients[$value] = $email;
        }

        function AddFileUploadField($file_field_name, $accepted_types, $max_size) {
            $this->fileupload_fields[] = array(
                "name" => $file_upload_name,
                "file_types" => $accepted_types,
                "maxsize" => $max_size);
        }

        function ProcessForm() {
            if (!isset($_POST['submitted'])) {
                return false;
            }
            if (!$this->Validate()) {
                $this->error_message = implode('<br/>', $this->errors);
                return false;
            }

            $this->CollectData();
            $ret = $this->SendFormSubmission();

            return $ret;
        }

        function RedirectToURL($url) {
            header("Location: $url");
            exit;
        }

        function GetErrorMessage() {
            return $this->error_message;
        }

        function GetSelfScript() {
            return htmlEntities($_SERVER['PHP_SELF']);
        }

        function GetName() {
            return $this->name;
        }

        function GetEmail() {
            return $this->email;
        }

        function GetMessage() {
            return htmlEntities($this->message, ENT_QUOTES, "UTF-8");
        }

        /* Private Functions */
    }
?>
