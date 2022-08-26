import {ApplicationController} from 'stimulus-use'
import router from "../utils/routing";
import {fetch, ok} from '../utils/http';

export default class extends ApplicationController {
    static targets = ['notifications', 'messages']
    static classes = ['hidden']

    connect() {
        super.connect();

        if (window.KBIN_LOGGED_IN) {
            this.updateCounter();
        }
    }

    async notification(event) {
        if (!this.hasNotificationsTarget) {
            return;
        }

        this.updateCounter();

        // if (window.notifyCounter) {
        //     clearTimeout(window.notifyCounter);
        // }
        //
        // window.notifyCounter = setTimeout(() => {
        //     try {
        //         this.updateCounter()
        //     } catch (e) {
        //     }
        // }, Math.floor(Math.random() * (10000 - 1000 + 1)) + 1000);
    }

    async updateCounter() {
        if (window.KBIN_LOGGED_IN) {
            const url = router().generate('ajax_fetch_user_notifications_count', {username: window.KBIN_USERNAME});

            let response = await fetch(url);

            response = await ok(response);
            response = await response.json();
        } else {
            const response = {
                count: 1
            };
        }

        if (response.count > 0) {
            let elem = this.notificationsTarget.getElementsByTagName('span')[0];
            elem.innerHTML = response.count;

            this.notificationsTarget.classList.remove(this.hiddenClass);
        }
    }

    message(event) {
        if (!this.hasMessagesTarget) {
            return;
        }

        let elem = this.messagesTarget.getElementsByTagName('span')[0];
        elem.innerHTML = parseInt(elem.innerHTML) + 1;

        this.messagesTarget.classList.remove(this.hiddenClass);
    }
}
