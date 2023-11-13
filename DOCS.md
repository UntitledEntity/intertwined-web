# [Intertwined API Documentation](https://docs.intertwined.solutions)

The official documentation for the intertwined API v1.0.0 (Unencrypted)

# API Functions

### Init
    Initiate an API session.

    Parameters (JSON):
    { 
        "type": "init", 
        "appid": "XXXXXXXX", // Your unique application ID.
        "hash": "hash" // The hash to verify.
    }

    Responses (JSON):

        Successful Response:
        { 
            "success": true, 
            "sessionid": "XXXXXXXX" // Your unique session ID.
        }

        Failed Response:
        {
            "success": false,
            "error": "Lorem Ipsum"
        }

    Possible errors:

        Application disabled - The application was disabled by the owner on the "application" dashboard tab.

        Unable to open session - Error within backend or your application. Contact an administrator if the error persists.

### Login

    Validate user credentials and get user data.
    
    Parameters (JSON):
    { 
        "type": "login", 
        "sid": "XXXXXXXX", // Unique ID returned after calling the Init function
        "hash": "hash", // OPTIONAL - Hash to verify
        "user": "username", // username of the user you're attempting to verify
        "pass": "password", // password of the user you're attempting to verify
        "hwid": "hwid" // OPTIONAL - HWID of the user you're attempting to verify. If this is the first time you are validating this user, this hwid will be recorded.
    }

    Responses (JSON):

        Successful response: 
        {
            "success": true, 
            "data": { 
                "user": "username", 
                "expiry": "XXXXXXXXXX", 
                "level": 1, 
                "ip": "255.255.255.255" 
            } 
        }

        Failed Response:
        {
            "success": false,
            "error": "Lorem Ipsum"
        }

    Possible Errors:

        Application disabled - The application was disabled by the owner on the 'application' dashboard tab.

        Incorrect session ID - The session ID you provided is invalid.

        Invalid hash - The hash you provided is invalid.

        blacklisted - The IP or HWID you are calling from is blacklisted.

        banned - The user is banned.

        subscription_expired - The user's subscription has expired.

        user_not_found - The username that you've provided is invalid.

        password_mismatch - The password that you've provided is invalid. 

        invalid_ip - The IP you are connecting from does not match the stored one.
        
        hwid_mismatch - The HWD you uploaded from does not match the stored one.

### LoginLicense

    Validate user credentials and get user data.
    
    Parameters (JSON):
    { 
        "type": "loginlicense", 
        "sid": "XXXXXXXX", // Unique ID returned after calling the Init function
        "hash": "hash", // OPTIONAL - Hash to verify
        "license": "license", // license you're attempting to verify
        "hwid": "hwid" // OPTIONAL - HWID of the user you're attempting to verify. If this is the first time you are validating this user, this hwid will be recorded.
    }

    Responses (JSON):

        Successful response: 
        {
            "success": true, 
            "data": { 
                "license": "license", 
                "expiry": "XXXXXXXXXX", 
                "level": 1, 
                "ip": "255.255.255.255" 
            } 
        }

        Failed Response:
        {
            "success": false,
            "error": "Lorem Ipsum"
        }

    Possible Errors:

        Application disabled - The application was disabled by the owner on the 'application' dashboard tab.

        Incorrect session ID - The session ID you provided is invalid.

        Invalid hash - The hash you provided is invalid.

        blacklisted - The IP or HWID you are calling from is blacklisted.

        banned - The license is banned.

        license_expired - The license's subscription has expired.

        invalid_license - The license that you've provided is invalid.

        invalid_ip - The IP you are connecting from does not match the stored one.
        
        hwid_mismatch - The HWD you uploaded from does not match the stored one.
        
### Register

    Registers a user and logs them in using the provided credentials.
    
    Parameters (JSON):
    { 
        "type": "register",
        "sid": "XXXXXXXX", // Unique ID returned after calling the Init function
        "hash": "hash", // OPTIONAL - Hash to verify
        "user": "username", // username of the new user you're creating
        "pass": "password", // password of the new user you're creating
        "license": "33923d3b2c7abcca78222144ddd19f10" // The generated license you are registerring this user with.
    }

    Responses (JSON):

        Successful response: 
        {
            "success": true, 
            "data": { 
                "user": "username", 
                "expiry": "XXXXXXXXXX", 
                "level": 1, 
                "ip": "255.255.255.255" 
            } 
        }

        Failed Response:
        {
            "success": false,
            "error": "Lorem Ipsum"
        }

    Possible Errors:

        Application disabled - The application was disabled by the owner on the 'application' dashboard tab.

        Incorrect session ID - The session ID you provided is invalid.

        Invalid hash - The hash you provided is invalid.

        blacklisted - The IP or HWID you are calling from is blacklisted.

        password_mismatch - The password that you've provided is the same as the user or is less than 4 characters.

        user_already_taken - The username you've provided is already taken by a user in the same application.

        invalid_license - The license you've provided doesn't exist.

        license_already_used - The license you've provided has already been claimed.

        expired_license - The licenses time limit has expired.

        invalid_level - There was an error in generating the license that you've provided.

### Upgrade
    
    Upgrades a users level and extends their expiry using a license.

    Parameters (JSON):
    { 
        'type': "upgrade", 
        'sid': "XXXXXXXX", // Unique ID returned after calling the Init function
        "hash": "hash", // OPTIONAL - Hash to verify
        "user": "username", // username of the user you're upgrading
        'license': "license" // the license you're upgrading the user to
    }

    Responses:

        Successful response: 
        { 
            'success': true, 
            'upgrade_data': { 
                'level': 1, 
                'expiry': "XXXXXXXXXX" 
            } 
        }

        Failed response:
        { 
            'success': false, 
            'error': "Lorem Ipsum" 
        }

    Possible errors:

        Application disabled - The application was disabled by the owner on the 'application' dashboard tab.

        Incorrect session ID - The session ID you provided is invalid.

        Invalid hash - The hash you provided is invalid.

        user_not_found - The username that you've provided is invalid.

        invalid_license - The license you've provided doesn't exist.

        license_already_used - The license you've provided has already been claimed.

        expired_license - The licenses time limit has expired.

        invalid_level - The level of the license is less than the level of the user.

### Webhook

    Calls a webhook URL and returns the content if successful.

    Parameters (JSON):
    {
        "type": "webhook", 
        "sid": "XXXXXXXX", // Unique ID returned after calling the Init function
        "hash": "hash", // OPTIONAL - Hash to verify
        "whid": "XXXXXXXX" // Webhook ID for the webhook.
    }

    Responses:

        Successful response:
        {
            'success': true, 
            "response": "content"
        }

        Failed response:
        {
            'success': false,
            "error": "lorem ipsum"
        }

    Possible errors:

        Incorrect session ID - The session ID you provided is invalid.
        Invalid hash - The hash you provided is invalid.
        Session is not authenticated - 'Login' or 'LoginLicense' has not been successfully executed for this session. (Optional security, configurable in dashboard)
        
### Variables

    Gets a server-variables data.

    Parameters (JSON):
    {
        "type": "get_var", 
        "sid": "XXXXXXXX", // Unique ID returned after calling the Init function
        "hash": "hash", // OPTIONAL - Hash to verify
        "var_id": "XXXXXXXX" // Variable ID for the variable.
    }

    Responses:

        Successful response:
        {
            'success': true, 
            "var": "data"
        }

        Failed response:
        {
            'success': false,
            "error": "lorem ipsum"
        }

    Possible errors:

        Incorrect session ID - The session ID you provided is invalid.
        Invalid hash - The hash you provided is invalid.
        Session is not authenticated - 'Login' or 'LoginLicense' has not been successfully executed for this session. (Optional security, configurable in dashboard)


### Check Validity

    Checks if the login function has been called for a session.

    Parameters (JSON):
    {
        "type": "check_validity", 
        'sid': "XXXXXXXX" 
    }

    Responses:

        Successful response:
        {
            'success': true, 
            'validity': true | false
        }

        Failed response:
        {
            'success': false,
            "error": "lorem ipsum"
        }

    Possible errors:

        Incorrect session ID - The session ID you provided is invalid.
