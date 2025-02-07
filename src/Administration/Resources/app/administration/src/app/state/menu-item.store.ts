/**
 * @sw-package framework
 * @deprecated tag:v6.7.0 - Will be replaced with Pinia store
 */

import type { Module } from 'vuex';
import type { menuItemAdd } from '@shopware-ag/meteor-admin-sdk/es/ui/menu';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export type MenuItemEntry = Omit<menuItemAdd, 'responseType' | 'locationId' | 'displaySearchBar'> & { moduleId: string };

interface MenuItemState {
    menuItems: MenuItemEntry[];
}

const MenuItemStore: Module<MenuItemState, VuexRootState> = {
    namespaced: true,

    state: (): MenuItemState => ({
        menuItems: [],
    }),

    mutations: {
        addMenuItem(state, { label, parent, position, moduleId }: MenuItemEntry) {
            state.menuItems.push({
                label,
                parent,
                position,
                moduleId,
            });
        },
    },
};

/**
 * @private
 */
export default MenuItemStore;
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export type { MenuItemState };
