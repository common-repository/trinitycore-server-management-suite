// Set maxlength value for your required fields
var maxlength = 32;
var minlength = 6;

var RecaptchaOptions =
{
    theme : 'custom',
    custom_theme_widget: 'recaptcha_widget'
};

/*
* You can pass three parameters this function
* Example : ValidateRequiredField(phone,"Telephone must be filled out!", "number");
* For string format no need to pass any strFormat.
*/
function ValidateRequiredField(field,alerttxt,strFormat)
{
		with (field)
		{
			if (value == null|| value == "")
			{
				field.style.background= "#99ccff";
				alert(alerttxt);
				return false;
			}
			else if (value.length > maxlength )
			{
				field.style.background= "#99ccff";
				alert('Max length is 32 characters');
				return false;
			}
			else if (strFormat == 'number' && isNaN(value) )
			{
				field.style.background= "#99ccff";
				alert(field.name + ' is not a number, Please put in numeric format');
				return false;
			}
			else
			{
				return true;
			}
		}
}

/*
 * Using the function you can validate the email functions
 * Example: ValidateEmailAddress(email,"Email is not in Valid format!")
 * Return true or false
 */
function ValidateEmailAddress(field, alerttxt)
{
	with (field)
	{
		apos=value.indexOf("@");
		dotpos=value.lastIndexOf(".");
		if (apos < 1 || dotpos-apos < 2)
		{
			alert(alerttxt);
			return false;
		}
		else
		{
			return true;
		}
	}
}	
	
function ValidatePasswordStrength(field,alerttext)
{
	with(field)
	{
        var pw = value;
        
        if(pw.length < minlength )
        {
            field.style.background= "#99ccff";
            alert('Minimum password length is 6 characters.');
            return false;
        }
        // at least one number, one lowercase and one uppercase letter
        // at least six characters
        var re = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])\w{6,}/;

        if(re.test(pw))
        {
            return true;
        }
        else
        {
            alert(alerttext);
            return false;
        }
    }
}
	

/* ValidateCompleteForm will parse the individual fields to ensure validation */
function ValidateCompleteForm(thisform)
{
    with (thisform)
    {
        if (ValidateRequiredField(thepassword,"A new password is required!")== false)
        {
            thepassword.focus();
            return false;
        }

        if (ValidateRequiredField(thepasswordrepeat,"You must repeat your password!")== false)
        {
            thepasswordrepeat.focus();
            return false;
        }

        if( ValidateRequiredField(recaptcha_response_field,"You must fill out the Captcha!")== false)
        {
            recaptcha_response_field.focus();
            return false;
        }
                
        if( thepassword.value != thepasswordrepeat.value)
        {
            alert("Passwords Do Not Match!");
            thepasswordrepeat.focus();
            return false;
        }
            
        if (ValidatePasswordStrength(thepassword,"Password is not strong enough, please review guidelines!")== false)
        {
            thepasswordrepeat.focus();
            return false;
        }
    }
}
