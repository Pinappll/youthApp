import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['selector']
    static values = {
        url: String
    }

    async update(event) {
        const form = this.element.closest('form');
        const formData = new FormData(form);
        
        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newTargetSelectors = doc.getElementById('targetSelectors');
            if (newTargetSelectors) {
                this.element.innerHTML = newTargetSelectors.innerHTML;
            }
        } catch (error) {
            console.error('Error updating form:', error);
        }
    }
}
