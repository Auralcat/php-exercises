let frmValidator = new Validator("contact-us");
frmValidator.EnableOnPageErrorDisplay();
frmValidator.EnableMsgsTogether();
frmValidator.addValidation("name", "req", "Please provide your name");
frmValidator.addValidation("email", "req", "Please provide your email address");
