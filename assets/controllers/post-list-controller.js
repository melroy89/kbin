import {Controller} from '@hotwired/stimulus';
import {useDispatch} from "stimulus-use";
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";

export default class extends Controller {
    static values = {
        magazineName: String
    };

    connect() {
        useDispatch(this)
    }

    async add(notification) {
        const magazineName = notification.detail.magazine.name;
        if (this.hasMagazineNameValue && this.magazineNameValue !== magazineName) {
            return;
        }

        try {
            const url = router().generate('ajax_fetch_post', {'id': notification.detail.id});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            const html = response.html;

            let div = document.createElement('div');
            div.innerHTML = html;

            this.element.prepend(div);
        } catch (e) {
        }
    }
}
