# JSON Web Token Cookie Login

[![MIT license](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/clarkwinkelmann/flarum-ext-jwt-cookie-login/blob/master/LICENSE.md) [![Latest Stable Version](https://img.shields.io/packagist/v/clarkwinkelmann/flarum-ext-jwt-cookie-login.svg)](https://packagist.org/packages/clarkwinkelmann/flarum-ext-jwt-cookie-login) [![Total Downloads](https://img.shields.io/packagist/dt/clarkwinkelmann/flarum-ext-jwt-cookie-login.svg)](https://packagist.org/packages/clarkwinkelmann/flarum-ext-jwt-cookie-login) [![Donate](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://www.paypal.me/clarkwinkelmann)

This extension implements quasi-stateless JWT-based sessions in Flarum.

The use case for this extension is implementing global login with an external platform serving as the master.
Your code is responsible for setting and updating the cookie, and Flarum will automatically connect and/or create users based on the content of the JWT.
No example code is available for the master implementation.
The information below should allow you to implement it in any programming language.

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
By default, user with ID `1` will be used but this can be customized in the admin settings.
The value must be the Flarum ID (MySQL auto-increment) and not the JWT subject ID.

The original Flarum session object (Symfony session) and cookie are not used for stateless authentication, however the cookie session is kept because Flarum and some extensions cannot work without it.
This session object is not invalidated during "login" and "logout" of the stateless JWT authentication, so there could be issues with extensions that rely on that object for other purposes than validation messages.

### Hidden Iframe

The hidden iframe offers a way to refresh the cookie in the background and optionally to provide auto login.

If the hidden iframe setting is set, the given URL will be loaded in a 0x0 iframe placed outside the browser viewport.

The iframe can use [`window.postMessage`](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage) to inform Flarum of a change in the session state.
The message can be sent at any time and any number of times.
You can use a loop repeatedly sending the current state if necessary.

Flarum will check for a change in the reported state and prompt the user to refresh the page if it changes.

If `{jwtSessionState: 'login'}` is sent while Flarum is logged out, Flarum will say the user has been automatically logged in and may refresh the page.

If `{jwtSessionState: 'logout'}` is sent while Flarum is logged in, Flarum will say the session has expired and the user may refresh the page.

If the time elapsed between Flarum boot and the `postMessage` is smaller than the configured "Auto Login Delay", the page will refresh without user interaction.

Switching user without going through logout state is current not supported.

Code example for the iframe:

```js
window.parent.postMessage({
  jwtSessionState: 'login',
}, 'https://myforum.mydomain.tld');
```

The last parameter should be set to the Flarum `origin`.
`'*'` can also be used but isn't recommended.

## Installation

    composer require clarkwinkelmann/flarum-ext-jwt-cookie-login

## Support

This extension is under **minimal maintenance**.

It was developed for a client and released as open-source for the benefit of the community.
I might publish simple bugfixes or compatibility updates for free.

You can [contact me](https://clarkwinkelmann.com/flarum) to sponsor additional features or updates.

Support is offered on a "best effort" basis through the Flarum community thread.

**Sponsors**: Dater.com

## Links

- [GitHub](https://github.com/clarkwinkelmann/flarum-ext-jwt-cookie-login)
- [Packagist](https://packagist.org/packages/clarkwinkelmann/flarum-ext-jwt-cookie-login)
- [Discuss](https://discuss.flarum.org/d/30632)
