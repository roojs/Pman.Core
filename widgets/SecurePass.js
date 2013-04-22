
//<script type="text/Javascript">


Ext.form.SecurePass = function(config) {
	// these go here, so the translation tool can replace them..
    this.errors = {
		PwdEmpty: "Please type a password, and then retype it to confirm.",
		PwdShort: "Your password must be at least 6 characters long. Please type a different password.",
		PwdLong: "Your password can't contain more than 16 characters. Please type a different password.",
		PwdBadChar: "The password contains characters that aren't allowed. Please type a different password.",
		IDInPwd: "Your password can't include the part of your ID. Please type a different password.",
		FNInPwd: "Your password can't contain your first name. Please type a different password.",
		LNInPwd: "Your password can't contain your last name. Please type a different password.",
        TooWeak: "Your password is Too Weak."
	},
    this.meterLabel = "Password strength:";
    this.pwdStrengths = ["Too Weak" , "Weak", "Medium", "Strong"];
    Ext.form.SecurePass.superclass.constructor.call(this, config);
}

Ext.extend(Ext.form.SecurePass, Ext.form.TextField, {
	/**
	 * @cfg {String/Object} errors A Error spec, or true for a default spec (defaults to
	 * {
	 *  PwdEmpty: "Please type a password, and then retype it to confirm.",
	 *  PwdShort: "Your password must be at least 6 characters long. Please type a different password.",
	 *  PwdLong: "Your password can't contain more than 16 characters. Please type a different password.",
	 *  PwdBadChar: "The password contains characters that aren't allowed. Please type a different password.",
	 *  IDInPwd: "Your password can't include the part of your ID. Please type a different password.",
	 *  FNInPwd: "Your password can't contain your first name. Please type a different password.",
	 *  LNInPwd: "Your password can't contain your last name. Please type a different password."
	 * })
	 */
	// private
	errors : {},
    
    imageRoot: '/',
	
	/**
	 * @cfg {String/Object} Label for the strength meter (defaults to
	 * 'Password strength:')
	 */
	// private
	meterLabel : '',

	/**
	 * @cfg {String/Object} pwdStrengths A pwdStrengths spec, or true for a default spec (defaults to
	 * ['Weak', 'Medium', 'Strong'])
	 */
	// private
	pwdStrengths : [],

	/**
	 * @cfg {String/Object} fieldsFilter A fieldsFilter spec, as [['field_name', 'error_id'], ...]
	 */
	// private
	fieldsFilter : [],

	// private
	strength : 0,

	// private
	_lastPwd : null,
    
	// private
	kCapitalLetter : 0,
	kSmallLetter : 1,
	kDigit : 2,
	kPunctuation : 3

    // private
   
})