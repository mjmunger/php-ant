/**
 * Created by michael on 9/23/17.
 */

function generatePassword() {
    var length = 12,
        charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
        retVal = "";
    for (var i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
    }
    return retVal;
}

function validateForm() {
    let ret = true;
    let errorBuffer = '';
    let errorList = document.getElementById('error-list');

    errorList.innerHTML = "";

    let fields = { server   : "Please enter the database server URI." , rootpass : "Please enter the database root password, so we can create the database." , database : "Please enter the database name for your application." , username : "Please enter the database username." , password : "Please enter the database password for the application user." , weburl   : "Please enter the full website URL.", webroot  : "Please enter the web applications document root.", adminfirst : "Please enter the first name of the admin user." , adminlast  : "Please enter the last name of the admin user." , adminuser  : "Please enter a username for the admin user." , adminpass  : "Please enter a password for the admin user." } ;

    for(let key in fields) {

        let elem = document.getElementById(key);

        console.log(elem.value);

        if(elem.value.length == 0) {
            let message = fields[key];
            elem.classList.add('wrong');
            ret = false;

            errorBuffer += "<p>" + message + "</p>";

        } else {
            elem.classList.remove('wrong');
        }
    }

    errorList.innerHTML = errorBuffer;
    return ret
}

