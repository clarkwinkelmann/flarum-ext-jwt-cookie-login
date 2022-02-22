import app from 'flarum/forum/app';
import {extend} from 'flarum/common/extend';
import LinkButton from 'flarum/common/components/LinkButton';
import SessionDropdown from 'flarum/forum/components/SessionDropdown';

app.initializers.add('jwt-cookie-login', () => {
    extend(SessionDropdown.prototype, 'items', function (items) {
        // TODO: only hide if in stateless JWT session
        items.remove('logOut');

        const href = app.forum.attribute('logoutRedirect');

        if (href) {
            items.add('logOutLink', LinkButton.component({
                icon: 'fas fa-sign-out-alt',
                href,
            }, app.translator.trans('core.forum.header.log_out_button')));
        }
    });
});
