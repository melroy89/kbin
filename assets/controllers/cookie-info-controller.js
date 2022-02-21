import {Controller} from '@hotwired/stimulus';
import Cookies from 'js-cookie';

export default class extends Controller {
    close() {
        Cookies.set('cookie-info', true);
        this.element.classList.add('d-none');
    }
}
