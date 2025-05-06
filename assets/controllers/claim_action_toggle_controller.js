import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['content'];

    toggle() {
        const content = this.contentTarget;

        content.classList.toggle('showing');

        if (content.classList.contains('showing')) {
            content.style.maxHeight = content.scrollHeight + 'px';
        } else {
            content.style.maxHeight = null;
        }
    }
}
