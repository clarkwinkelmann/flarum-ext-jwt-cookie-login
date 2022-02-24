# JWT Cookie Login

[![MIT license](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/clarkwinkelmann/flarum-ext-jwt-cookie-login/blob/master/LICENSE.md) [![Latest Stable Version](https://img.shields.io/packagist/v/clarkwinkelmann/flarum-ext-jwt-cookie-login.svg)](https://packagist.org/packages/clarkwinkelmann/flarum-ext-jwt-cookie-login) [![Total Downloads](https://img.shields.io/packagist/dt/clarkwinkelmann/flarum-ext-jwt-cookie-login.svg)](https://packagist.org/packages/clarkwinkelmann/flarum-ext-jwt-cookie-login) [![Donate](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://www.paypal.me/clarkwinkelmann)

This extension implements stateless JWT-based sessions in Flarum.

Users are matched through the `jwt_subject` column in the database that is matched to the token's `sub` value.

By default, tokens are validated using Firebase keys but custom keys can also be used.

A callback hook can be defined to obtain default values for new users from an external API.

Users can be edited via their JWT Subject ID by using the `PATCH /api/jwt/users/<sub>` endpoint.
It works exactly the same way as `PATCH /api/users/<id>`.

By default, all accounts will be automatically enabled. You can change this behaviour by returning `"isEmailConfirmed": false` in the registration hook.

Currently, the Flarum user with ID 1 is hard-coded as the actor that creates new users during registration.
Make sure the user with Flarum ID 1 exists and is an administrator.

The Symfony session object and cookie are not used for stateless authentication, however the cookie session is kept because Flarum and some extensions cannot work without it.
This session object is not invalidated during "login" and "logout" of the stateless JWT authentication, so there could be issues with extensions that rely on that object for other purposes than validation messages.

## Installation

    composer require clarkwinkelmann/flarum-ext-jwt-cookie-login

## Support

This extension is under **minimal maintenance**.

It was developed for a client and released as open-source for the benefit of the community.
I might publish simple bugfixes or compatibility updates for free.

You can [contact me](https://clarkwinkelmann.com/flarum) to sponsor additional features or updates.

Support is offered on a "best effort" basis through the Flarum community thread.

## Links

- [GitHub](https://github.com/clarkwinkelmann/flarum-ext-jwt-cookie-login)
- [Packagist](https://packagist.org/packages/clarkwinkelmann/flarum-ext-jwt-cookie-login)
