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

        function SendFormSubmission() {
            $this->CollectConditionalRecipients();
            $this->mailer->CharSet = 'utf-8';
            $this->mailer->Subject = "Contact form submission from $this->name";
            $this->mailer->From = $this->GetFromAddress();
            $this->mailer->FromName = $this->name;
            $this->mailer->AddReplyTo($this->email);

            $message = $this->ComposeFormToEmail();

            $regex_pat = '/<(head|title|style|script)[^>]*>.?<\/\\1>/s';
            $textMsg = trim(strip_tags(preg_replace($regex_pat, '', $message)));

            $this->mailer->AltBody =
                @html_entity_decode($textMsg, ENT_QUOTES, "UTF-8");

            $this->mailer->MsgHTML($message);
            $this->AttachFiles();

            if (!$this->mailer->Send()) {
                $this->add_error("Failed sending email!");
                return false;
            }

            return true;
        }

        function CollectConditionalRecipients() {
            if (count($this->arr_conditional_recipients) > 0 &&
                !empty($this->conditional_field) &&
                !empty($_POST[$this->conditional_field])) {

                foreach($this->arr_conditional_recipients as $condn => $rec) {
                    if (strcasecmp($condn, $_POST[$this->conditional_field]) == 0 &&
                        !empty($rec)) {

                        $this->AddRecipient($rec);
                    }
                }
            }
        }

        /* Internal variables that you don't want to appear in the email.
           Add those variables in the array. */
        function IsInternalVariable($varname) {
            $arr_internal_vars =
                array(
                      'scaptcha',
                      'submitted',
                      $this->GetSpamTrapInputName(),
                      $this->GetFormIDInputName()
                      );
            return in_array($varname, $arr_internal_vars);
        }

        function FormSubmissionToMail() {
            $ret_str = '';
            foreach($_POST as $key => $value) {
                if (!$this->IsInternalVariable($key)) {
                    $value = htmlEntities($value, ENT_QUOTES, "UTF-8");
                    $value = nl2br($value);
                    $key = ucfirst($key);
                    $ret_str .= "<div class='label'>" . $key .
                        ": </div><div class='value>" . $value . "</div>\n";
                }
            }

            foreach($this->fileupload_fields as $upload_field) {
                $field_name = $upload_field["name"];
                if (!$this->IsFileUpload($field_name)) {
                    continue;
                }

                $filename = basename($_FILES[$field_name]['name']);

                $ret_str .= "<div class='label'>File upload '$field_name'" .
                    ": </div><div class='value'>$filename</div>\n";
            }

            return $ret_str;
        }

        function ExtraInfoToMail() {
            $ret_str = '';

            $ip = $_SERVER['REMOTE_ADDR'];
            $ret_str = "<div class='label'>IP address of the submitter:</div>" .
                "<div class='value'>$ip</div>\n";

            return $ret_str;
        }

        function GetMailStyle() {
           $ret_str = "\n<style>" .
               "body, .label, .value { font-family: Arial, Verdana; } " .
               ".label { font-weight: bold; margin-top: 5px; font-size: 1em; " .
               "color: #333; }" .
               ".value { margin-bottom: 15px; font-size: 0.8em; " .
               "padding-left: 5px; }" .
               "</style>\n";

           return $ret_str;
        }

        function GetHTMLHeaderPart() {
           $ret_str = "<!DOCTYPE html>\n<html lang='pt_BR'>" .
               "<head><title></title>" .
               "<meta http-equiv=Content-Type " .
               "content='text/html' charset='utf-8'>";
           $ret_str .= $this->GetMailStyle();
           $ret_str .= "</head><body>";
           return $ret_str;
        }

        function GetHTMLFooterPart() {
            $ret_str = "</body></html>";
            return $ret_str;
        }

        function ComposeFormToEmail() {
            $header = $this->GetHTMLHeaderPart();
            $form_submission = $this->FormSubmissionToMail();
            $extra_info = $this->ExtraInfoToMail();
            $footer = $this->GetHTMLFooterPart();

            $message = $header . "Submission from 'contact us' " .
                "form:<p>$form_submission</p><hr>" .
                $extra_info . $footer;

            return $message;
        }

        function AttachFiles() {
            foreach($this->fileupload_fields as $upld_field) {
                $field_name = $upld_field["name"];
                if (!$this->IsFileUploaded($field_name)) {
                    continue;
                }

                $filename = basename($_FILES[$field_name]['name']);

                $this->mailer->AddAttachment($_FILES[$field_name]["tmp_name"],
                                             $filename);
            }
        }

        function GetFromAddress() {
            if (!empty($this->from_address)) {
                return $this->from_address;
            }

            $host = $_SERVER['SERVER_NAME'];

            $from = "nobody@$host";
            return $from;
        }

    }
?>
