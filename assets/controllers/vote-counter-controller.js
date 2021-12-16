import {Controller} from '@hotwired/stimulus';
import { useDispatch } from 'stimulus-use'

export default class extends Controller {
    static targets = ['upVotes', 'downVotes'];
    static values = {
        subjectId: Number,
    };

    connect() {
        useDispatch(this)
    }

    refresh(notification) {
        if(this.subjectIdValue === notification.detail.id){
            this.upVotesTarget.textContent = notification.detail.up;
            this.downVotesTarget.textContent = notification.detail.down;
        }
    }
}
