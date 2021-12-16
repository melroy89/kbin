import {Controller} from '@hotwired/stimulus';
import {useDispatch} from "stimulus-use";

export default class extends Controller {
    static targets = ['lenght'];
    static values = {
        subjectId: Number,
    };

    connect() {
        useDispatch(this)
    }

    increase(notification) {
        if (this.subjectIdValue === notification.detail.subject.id && this.hasLenghtTarget) {
            this.lenghtTarget.textContent = Number(this.lenghtTarget.textContent) + 1;
        }
    }
}
