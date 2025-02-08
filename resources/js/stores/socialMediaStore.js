import { defineStore } from 'pinia';

export const useSocialMediaStore = defineStore('socialMedia', {
    state: () => ({
        facebook: {
            connected: false,
            connecting: false,
            platformId: null,
            pages: []
        },
        instagram: {
            connected: false,
            connecting: false,
            platformId: null,
            accounts: []
        }
    }),

    actions: {
        initializeFromStorage() {
            try {
                const stored = localStorage.getItem('socialMediaState');
                if (stored) {
                    const parsedState = JSON.parse(stored);
                    // Update store with saved state
                    this.$patch(parsedState);
                }
            } catch (error) {
                console.error('Failed to initialize from storage:', error);
            }
        },

        setFacebookConnection(data) {
            this.facebook = {
                connected: true,
                connecting: false,
                platformId: data.platform_id,
                pages: data.pages || []
            };
            this.persistToStorage();
        },

        setInstagramConnection(data) {
            this.instagram = {
                connected: true,
                connecting: false,
                platformId: data.platform_id,
                accounts: data.accounts || []
            };
            this.persistToStorage();
        },

        clearFacebookConnection() {
            this.facebook = {
                connected: false,
                connecting: false,
                platformId: null,
                pages: []
            };
            this.persistToStorage();
        },

        clearInstagramConnection() {
            this.instagram = {
                connected: false,
                connecting: false,
                platformId: null,
                accounts: []
            };
            this.persistToStorage();
        },

        persistToStorage() {
            try {
                localStorage.setItem('socialMediaState', JSON.stringify({
                    facebook: this.facebook,
                    instagram: this.instagram
                }));
            } catch (error) {
                console.error('Failed to persist to storage:', error);
            }
        }
    }
});
