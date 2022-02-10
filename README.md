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

    // show user links
    {
        route: "/show_public_links",
        method: "get",
        request parameters: {
            "username":"hb"
        },
        response: {
            // if username is invalid
            "message" : "user not found"

            <!-- else -->
            "links": [{link object}, {link object}, ..] //this will be in sorted order and active links
        }
    },

    // add users to waiting list to use our product
    {
        route: "/early_access",
        method: "post",
        request parameters: {
            "name" : "Harsh Bhatt", //not required from db but need to pass
            "email" : "bhattharsh610@gmail.com"
        },
        response: {
            "message" : "information Saved"
        }
    },

    // change user password
    {
        route: "/change_password",
        method: "post",
        request parameters: {
            "old_password" : "Harsh123",
            "new_password" : "Azova123",
            "confirm_password" : "Azova123"
        },
        response: {
            <!-- if validation failes -->
            "message" : "validation error messages"

            <!-- old password is wrong -->
            "message" : "Check your old password."

            <!-- new password is same as old password -->
            "message" : "Please enter a password which is not similar then current password."

            <!-- else -->
            "message" : "Password updated successfully."
        }
    },

    // get current login user details
    {
        route: "/user",
        method: "get",
        request parameters: {
        },
        response: {
            "user" : "{will return current user object as mentioned in database}"
        }
    },

    // logout current user
    {
        route: "/logout",
        method: "post",
        request parameters: {
        },
        response: {
            "success": "logout_success"
        }
    },

    // api to save basic user information
    {
        route: "/setup_user",
        method: "post",
        request parameters: {
            "name" : "Pradhyuman", // required
            "categories" : ['art', 'web app', 'law'], //optional
            "email" : "pradhyuman@gmail.com", //optional
            "username" : "pradhu", //optional
            "is_deleted" : "0", //optional 1 - to delete user
            "email" : "pradhyuman@gmail.com", //optional
        },
        response: {
            <!-- validation error -->
            "message": "name must not be empty || name must be atleat 3 characters"

            <!-- if we have passed email or username already in our database -->
            "message": "Email Or username Already exists"

            <!-- else -->
            "message" : "information Saved"
        }
    },

    // save user profile information
    {
        route: "/profile",
        method: "post",
        request parameters: {
            "profile_name" : "prdPaas" // min 3 characters max 20 characters
            "bio" : "this is having limit of 120 characters"
            "cover_image" : //pass file here mimes:jpg,jpeg,png
            "profile_image" : //pass file here mimes:jpg,jpeg,png
        },
        response: {
            <!-- validation error -->
            "message": "respective validation error messages" 
            /* 
            [
                "Profile name length must be greater than 3 characters.",
                "Profile name length must be less than 20 characters.",
                "Profile bio can't be more than 120 characters Long.",
                "Must be Image file"
            ]
            */

            <!-- if user not found -->
            "message": "user not found" 

            <!-- else -->
            "message": "information Saved" 
        }
    },

    // api to get profession categories.
    {
        route: "/getCategories",
        method: "get",
        request parameters: {
        },
        response: {
            "categories" : [{categories object}, {categories object}, ...]
        }
    },

    // api to delete link.
    {
        route: "/delete_link",
        method: "get",
        request parameters: {
            "link_id" : "_jhvfdsvkjfdvfd"
            "username" : "pradhu"
        },
        response: {
            <!-- if user not found -->
            "message": "user not found" 

            <!-- link exist -->
            "message" : "link deleted successfully"

            <!-- else -->
            "message" : "something went wrong"
        }
    },

    // api to save link information.
    {
        route: "/save_link",
        method: "post",
        request parameters: {
            "link_id" : "_fajdsvjadsk", // pass if want to edit link
            "username" : "pradhu", // unique username
            "title" : "Google",
            "link_url" : "https://www.google.com",
            "active" : "1", // 0- inactive 1 - active
            "link_image" : // pass file here
        },
        response: {
            "message" : "Details Has been saved",
            "link_id" : "_fajdsvjadsk",
            "link_image" : "photo url"
        }
    },

    // list user links.
    {
        route: "/list_links",
        method: "get",
        request parameters: {
            "username" : "username", // unique username
        },
        response: {
            <!-- if user not found -->
            "message": "user not found" 

            <!-- else -->
            "links": [{link object}, {link object}, ..] //this will be in sorted order
        }
    },

    // sort user links.
    {
        route: "/sort_links",
        method: "post",
        request parameters: {
            "link_ids" : "["_bhdsabcdsabc", "_nacjsdbcj"], //in short array of link ids
        },
        response: {
            "message": "links are sorted successfully" 
        }
    },
]
