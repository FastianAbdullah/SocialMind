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
            accessToken: null,
            platformId: null
        },
        linkedin: {
            connected: false,
            connecting: false,
            platformId: null,
            userPlatformId: null
        }
    }),

    actions: {
        // Initialize the Storage.
        initializeFromStorage() {
            const stored = localStorage.getItem('socialMediaState');
            if (stored) {
                this.$patch(JSON.parse(stored));
            }
        },

        // Persist the Storage.
        persistToStorage() {
            localStorage.setItem('socialMediaState', JSON.stringify({
                facebook: this.facebook,
                linkedin: this.linkedin
                // ... other platforms
            }));
        },
        
        // Set the Facebook Connection.
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

        // Set the Linkedin Connection.
        setLinkedinConnection(data) {
            this.linkedin = {
                connected: true,
                connecting: false,
                platformId: data.platform_id,
                userPlatformId: data.user_platform_id
            };
            this.persistToStorage();
        },

        // Clear the Facebook Connection.
        clearFacebookConnection() {
            this.facebook = {
                connected: false,
                connecting: false,
                platformId: null,
                pages: []
            };
            this.persistToStorage();
        },

        // Clear the Linkedin Connection.
        clearLinkedinConnection() {
            this.linkedin = {
                connected: false,
                connecting: false,
                platformId: null,
                userPlatformId: null
            };
            this.persistToStorage();
        },

        // Clear the Social Media Store.
        clearSocialMediaStore() {
            this.facebook = {
                connected: false,
                connecting: false,
                platformId: null,
                pages: []
            };
            this.linkedin = {
                connected: false,
                connecting: false,
                platformId: null,
                userPlatformId: null
            };
            this.persistToStorage();
        }
    },

    getters: {
        isConnected: (state) => (platform) => {
            return state[platform]?.connected || false;
        }
    }
});
