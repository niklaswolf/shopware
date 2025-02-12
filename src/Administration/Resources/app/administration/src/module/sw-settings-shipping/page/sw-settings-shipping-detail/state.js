/**
 * @sw-package checkout
 * @deprecated tag:v6.7.0 - Will be replaced with Pinia store
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    namespaced: true,

    state() {
        return {
            shippingMethod: {},
            currencies: [],
            restrictedRuleIds: [],
        };
    },

    mutations: {
        setShippingMethod(state, shippingMethod) {
            state.shippingMethod = shippingMethod;
        },
        setCurrencies(state, currencies) {
            state.currencies = currencies;
        },
        setRestrictedRuleIds(state, restrictedRuleIds) {
            state.restrictedRuleIds = restrictedRuleIds;
        },
    },

    getters: {
        shippingPriceGroups(state) {
            if (!state.shippingMethod.prices) {
                return {};
            }

            const shippingPriceGroups = {};

            state.shippingMethod.prices.forEach((shippingPrice) => {
                let key = shippingPrice.ruleId;
                if (shippingPrice._inNewMatrix) {
                    key = 'new';
                }
                if (!shippingPriceGroups[key]) {
                    shippingPriceGroups[key] = {
                        isNew: key === 'new',
                        ruleId: shippingPrice.ruleId,
                        rule: shippingPrice.rule,
                        calculation: shippingPrice.calculation,
                        prices: [],
                    };
                }

                shippingPriceGroups[key].prices.push(shippingPrice);
            });

            return shippingPriceGroups;
        },

        defaultCurrency(state) {
            return state.currencies.find((currency) => currency.isSystemDefault);
        },

        usedRules(state, getters) {
            return Object.keys(getters.shippingPriceGroups);
        },

        unrestrictedPriceMatrixExists(state) {
            return state.shippingMethod.prices.some((shippingPrice) => {
                return shippingPrice.ruleId === null;
            });
        },

        newPriceMatrixExists(state, getters) {
            return getters.shippingPriceGroups.hasOwnProperty('new');
        },
    },
};
