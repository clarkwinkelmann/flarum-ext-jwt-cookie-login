# JWT Cookie Login

[![MIT license](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/clarkwinkelmann/flarum-ext-jwt-cookie-login/blob/master/LICENSE.md) [![Latest Stable Version](https://img.shields.io/packagist/v/clarkwinkelmann/flarum-ext-jwt-cookie-login.svg)](https://packagist.org/packages/clarkwinkelmann/flarum-ext-jwt-cookie-login) [![Total Downloads](https://img.shields.io/packagist/dt/clarkwinkelmann/flarum-ext-jwt-cookie-login.svg)](https://packagist.org/packages/clarkwinkelmann/flarum-ext-jwt-cookie-login) [![Donate](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://www.paypal.me/clarkwinkelmann)

This extension implements stateless JWT-based sessions in Flarum.

Users are matched through the `jwt_subject` column in the database that is matched to the token's `sub` value.

By default, tokens are validated using Google Firebase public keys (automatically retrieved and cached from Google servers) but custom keys can also be used.

A callback hook can be defined to obtain default values for new users from an external API.

The JWT subject ID for the hook call can be retrieved by using the replacement code `{uid}` as part of the hook URL, by reading the JWT in the `Authorization` header or by reading the `data.id` value in the hook JSON POST body.

The hook should return a [JSON:API](https://jsonapi.org/) compliant object describing the Flarum user attributes.
These attributes will be passed internally to `POST /api/users` so any attribute added by an extension can also be provided.

```json
{
  "data": {
    "attributes": {
      "username": "example",
      "email": "example@app.tld"
    }
  }
}
```

The validity of the hook request can be checked via the `Authorization` header.
It will contain `Token <JWT token>` by default, but can be customized to a hard-coded secret token via the admin settings.
The custom header setting will be applied verbatim as the header value, without any added prefix (i.e., `Token ` is not added).

Users can be edited via their JWT subject ID by using the `PATCH /api/jwt/users/<sub>` endpoint.
It works exactly the same way as `PATCH /api/users/<id>` but takes the JWT subject ID instead of Flarum ID.

By default, all accounts will be automatically enabled.
You can change this behaviour by returning `"isEmailConfirmed": false` attribute in the registration hook.

An admin user is used internally to call the REST API that creates new Flarum users.
By default, user with ID 1 will be used but this can be customized in the admin settings.
The value must be the Flarum ID (MySQL auto-increment) and not the JWT subject ID.

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
