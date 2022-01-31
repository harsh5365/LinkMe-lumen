# Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Official Documentation

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).

## Contributing

Thank you for considering contributing to Lumen! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Security Vulnerabilities

If you discover a security vulnerability within Lumen, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

ln -s /path/to/laravel/storage/app/public /path/to/laravel/public/storage
<!-- file url concat dev.welovecoders.com/storage/ -->

main url: https://dev.welovecoders.com/api
[
    // login
    {
        route: "/login",
        method: "post",
        request parameters: {
            "email": "bhattharsh610@gmail.com",
            "password": "Azova123"
        },
        response: {
            'token' => hfuhfeekjcndjhvudfhv, // api token
            'first_login' => 0, // 1- if first time login
            'username' => "hb"
        }
    },

    // registration
    {
        route: "/register",
        method: "post",
        request parameters: {
            "username": "hb" // must be unique throughout the database. will be used as custom url for links
            "email": "bhattharsh610@gmail.com", // must be unique throughout the database
            "password": "Azova123"
        },
        response: {
            "message" : "Activate your Account using activation link."
        }
    },

    // reset password request by putting email address. this should send password reset link to user email
    {
        route: "/password/reset-request",
        method: "post",
        request parameters: {
            "email":"pradhu619@gmail.com"
        },
        // if email address is valid
        response: {
            "message" : "generated Code Please check mail and proceed."
        }
    },

    // reset password by adding new password.
    {
        route: "/password/reset",
        method: "post",
        request parameters: {
            "token":"0336fe66a50fb591c46fe9718dc6736a65cfa804dd5ef73e20f078ef9229dbae",
            "email":"pradhu619@gmail.com",
            "password":"Admin@123",
            "confirm_password":"Admin@123"
        },
        // if details are valid
        response: {
            "message" : "Your Password had beeen reset successfully."
        }
    },

    // verify user account. this api will be need to use after user register to our app and then receive email with this link to confirm account.
    {
        route: "/verify_account",
        method: "get",
        request parameters: {
            "email":"yomena8645@firmjam.com",
            "verify_token":"vWsaGGVpFC"
        },
        response: {
            // if details are valid
            "message" : "Account Activated Successfully."
            // if verify token is invalid
            "message" : "Code mismatch please try again."
            // if email is invalid
            "message" : "No user found."
        }
    },

    // if in some case, user want to resend verification token or not received any, thsn need to use this
    {
        route: "/verify_mail_resend",
        method: "get",
        request parameters: {
            "email":"yomena8645@firmjam.com"
        },
        response: {
            // if email is valid and user is inactive yet
            "message" : "Mail has been sent."
            // if email is invalid
            "message" : "No user found."
        }
    },

    // if in some case, user want to resend verification token or not received any, thsn need to use this
    {
        route: "/show_public_links",
        method: "get",
        request parameters: {
            "username":"hb"
        },
        response: {
            // if username is invalid
            "message" : "user not found"
        }
    },
]
