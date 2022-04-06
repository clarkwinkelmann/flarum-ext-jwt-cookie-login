import app from 'flarum/forum/app';
import {extend} from 'flarum/common/extend';
import Button from 'flarum/common/components/Button';
import LinkButton from 'flarum/common/components/LinkButton';
import SessionDropdown from 'flarum/forum/components/SessionDropdown';
import ForumApplication from 'flarum/forum/ForumApplication';

app.initializers.add('jwt-cookie-login', () => {
    extend(SessionDropdown.prototype, 'items', function (items) {
        const href = app.forum.attribute<string | false>('logoutRedirect');

        // False is used to explicitly say the logout button should be hidden without any replacement
        if (href || href === false) {
            items.remove('logOut');
        }

        if (href) {
            items.add('logOutLink', LinkButton.component({
                icon: 'fas fa-sign-out-alt',
                href,
            }, app.translator.trans('core.forum.header.log_out_button')));
        }
    });

    extend(ForumApplication.prototype, 'mount', function () {
        const url = app.forum.attribute<string>('jwtIframe');

        if (!url) {
            return;
        }

        let bootTime = new Date();
        let hasPromptedLogin = false;
        let hasPromptedLogout = false;

        const parsedUrl = new URL(url);

        window.addEventListener('message', (event) => {
            if (event.origin !== parsedUrl.origin) {
                return;
            }

            if (typeof event.data !== 'object' || !event.data.hasOwnProperty('jwtSessionState')) {
                return;
            }

            const state = event.data.jwtSessionState;

            function showRefreshAlert(type: string, translation: string) {
                app.alerts.show({
                    type,
                    controls: [
                        Button.component({
                            className: 'Button Button--link',
                            onclick() {
                                window.location.reload();
                            },
                        }, app.translator.trans('clarkwinkelmann-jwt-cookie-login.forum.alert.refresh')),
                    ],
                }, app.translator.trans('clarkwinkelmann-jwt-cookie-login.forum.alert.' + translation));
            }

            switch (state) {
                case 'login':
                    if (app.session.user || hasPromptedLogin) {
                        return;
                    }

                    // After how many milliseconds should the page refresh automatically if the user was auto-connected
                    if ((new Date()).getTime() - bootTime.getTime() < app.forum.attribute<number>('autoLoginDelay')) {
                        window.location.reload();
                        return;
                    }

                    hasPromptedLogin = true;

                    showRefreshAlert('success', 'login');

                    break;
                case 'logout':
                    if (!app.session.user || hasPromptedLogout) {
                        return;
                    }

                    hasPromptedLogout = true;

                    showRefreshAlert('error', 'logout');

                    break;
            }
        }, false);

        const iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.width = '0';
        iframe.height = '0';
        iframe.style.position = 'absolute';
        iframe.style.top = '-1000px;'
        iframe.style.left = '-1000px';

        document.body.appendChild(iframe);
    });
});
