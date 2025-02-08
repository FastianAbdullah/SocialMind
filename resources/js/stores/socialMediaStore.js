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
            accessToken: null,
            platformId: null
        }
    }),

    actions: {
        setFacebookConnection(data) {
            this.facebook = {
                connected: true,
                connecting: false,
                platformId: data.platform_id,
                pages: data.pages || []
            };

            // Persist to localStorage
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

        initializeFromStorage() {
            const stored = localStorage.getItem('socialMediaState');
            if (stored) {
                this.$patch(JSON.parse(stored));
            }
        },

        persistToStorage() {
            localStorage.setItem('socialMediaState', JSON.stringify({
                facebook: this.facebook
                // ... other platforms
            }));
        }
    },

    getters: {
        isConnected: (state) => (platform) => {
            return state[platform]?.connected || false;
        }
    }
});
