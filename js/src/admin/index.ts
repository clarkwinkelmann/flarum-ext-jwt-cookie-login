import app from 'flarum/admin/app';

app.initializers.add('jwt-cookie-login', () => {
    app.extensionData
        .for('clarkwinkelmann-jwt-cookie-login')
        .registerSetting({
            setting: 'jwt-cookie-login.cookieName',
            type: 'text',
            label: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.cookieName'),
        })
        .registerSetting({
            setting: 'jwt-cookie-login.audience',
            type: 'text',
            label: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.audience'),
            help: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.audienceHelp'),
        })
        .registerSetting({
            setting: 'jwt-cookie-login.publicKey',
            type: 'textarea',
            label: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.publicKey'),
            help: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.publicKeyHelp'),
        })
        .registerSetting({
            setting: 'jwt-cookie-login.publicKeyAlgorithm',
            type: 'text',
            label: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.publicKeyAlgorithm'),
            help: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.publicKeyAlgorithmHelp'),
        })
        .registerSetting({
            setting: 'jwt-cookie-login.usernameTemplate',
            type: 'text',
            label: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.usernameTemplate'),
            help: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.usernameTemplateHelp', {
                sub: '{sub}',
            }),
        })
        .registerSetting({
            setting: 'jwt-cookie-login.emailTemplate',
            type: 'text',
            label: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.emailTemplate'),
            help: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.emailTemplateHelp', {
                sub: '{sub}',
            }),
        })
        .registerSetting({
            setting: 'jwt-cookie-login.registrationHook',
            type: 'text',
            label: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.registrationHook'),
            help: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.registrationHookHelp', {
                sub: '{sub}',
            }),
        })
        .registerSetting({
            setting: 'jwt-cookie-login.logoutRedirect',
            type: 'text',
            label: app.translator.trans('clarkwinkelmann-jwt-cookie-login.admin.settings.logoutRedirect'),
        });
});
