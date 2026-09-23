import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values  = { rate: Number };
    static targets = ['purchaseUsd', 'purchaseArs', 'retailUsd', 'retailArs'];

    purchaseUsdChanged() {
        this.#fill(this.purchaseUsdTarget, this.purchaseArsTarget);
    }

    retailUsdChanged() {
        this.#fill(this.retailUsdTarget, this.retailArsTarget);
    }

    #fill(usdInput, arsTarget) {
        const usd = parseFloat(usdInput.value) || 0;
        arsTarget.value = usd > 0 ? (usd * this.rateValue).toFixed(2) : '';
    }
}
