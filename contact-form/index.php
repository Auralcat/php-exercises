<!doctype HTML>
<html lang="pt_BR">
<?php
$cssFrameworkLink = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/js/bootstrap.min.js";
$pageTitle = "Sample Contact Form";
?>

<head>
    <meta charset="UTF-8" />
    <title>Sample Contact Form</title>
    <link href=<?php echo $cssFrameworkLink; ?> rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js" async></script>
    <script type="text/javascript" src="validate-fields.js"></script>
</head>

<body>
    <form id="contact-us" action="" method="post">
        <fieldset>
            <legend>Contact Us</legend>
            <input id="submitted" name="submitted" type="hidden" value="1" />

            <label for="name">Your Full Name*:</label><br/>
            <input id="name" name="name" type="text" value="" maxlength="50" /> <br/>

            <label for="email">Email Address*:</label><br/>
            <input id="email" email="email" type="text" value="" maxlength="50" /> <br/>

            <label for="phone">Phone number*:</label><br/>
            <input id="phone" phone="phone" type="text" value="" maxlength="15" /> <br/>

            <label for="message">Message:</label><br/>
            <textarea id="message" cols="50" id="" name="message" rows="10"></textarea>

            <input name="Submit" type="submit" value="Submit" />
        </fieldset>
    </form>
</body>

</html>
